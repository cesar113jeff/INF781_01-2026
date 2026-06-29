import {
  Injectable,
  ConflictException,
  UnauthorizedException,
  NotFoundException,
} from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { ConfigService } from '@nestjs/config';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import * as argon2 from 'argon2';
import { Request, Response } from 'express';
import { User } from '../users/entities/user.entity';
import { RefreshToken } from '../entities/refresh-token.entity';
import { RegisterDto } from './dto/register.dto';
import { LoginDto } from './dto/login.dto';

@Injectable()
export class AuthService {
  constructor(
    @InjectRepository(User)
    private readonly userRepository: Repository<User>,
    @InjectRepository(RefreshToken)
    private readonly refreshTokenRepository: Repository<RefreshToken>,
    private readonly jwtService: JwtService,
    private readonly configService: ConfigService,
  ) {}

  async register(
    registerDto: RegisterDto,
    req: Request,
    res: Response,
  ) {
    const existing = await this.userRepository.findOne({
      where: { email: registerDto.email },
    });
    if (existing) {
      throw new ConflictException('Email already registered');
    }

    const hashedPassword = await argon2.hash(registerDto.password);
    const user = this.userRepository.create({
      email: registerDto.email,
      password: hashedPassword,
    });
    const savedUser = await this.userRepository.save(user);

    const { accessToken, refreshToken } = await this.issueTokenPair(
      savedUser.id,
      savedUser.email,
      req,
    );
    this.setRefreshTokenCookie(res, refreshToken);

    return {
      id: savedUser.id,
      email: savedUser.email,
      accessToken,
    };
  }

  async login(loginDto: LoginDto, req: Request, res: Response) {
    const user = await this.userRepository.findOne({
      where: { email: loginDto.email },
    });
    if (!user) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const passwordValid = await argon2.verify(user.password, loginDto.password);
    if (!passwordValid) {
      throw new UnauthorizedException('Invalid credentials');
    }

    const { accessToken, refreshToken } = await this.issueTokenPair(
      user.id,
      user.email,
      req,
    );
    this.setRefreshTokenCookie(res, refreshToken);

    return {
      id: user.id,
      email: user.email,
      accessToken,
    };
  }

  async refresh(userId: string, sessionId: string, refreshToken: string) {
    const session = await this.refreshTokenRepository.findOne({
      where: { id: sessionId, userId, revoked: false },
    });

    if (!session) {
      throw new UnauthorizedException('Session revoked or not found');
    }

    const tokenValid = await argon2.verify(session.hashedToken, refreshToken);
    if (!tokenValid) {
      await this.revokeAllUserSessions(userId);
      throw new UnauthorizedException(
        'Refresh token reuse detected. All sessions revoked.',
      );
    }

    const user = await this.userRepository.findOne({
      where: { id: userId },
    });
    if (!user) {
      throw new UnauthorizedException('User not found');
    }

    const newRefreshToken = this.signRefreshToken(userId, sessionId);
    session.hashedToken = await argon2.hash(newRefreshToken);
    await this.refreshTokenRepository.save(session);

    const accessToken = this.signAccessToken(userId, user.email);

    return { accessToken, refreshToken: newRefreshToken };
  }

  async logout(sessionId: string) {
    await this.refreshTokenRepository.update(
      { id: sessionId },
      { revoked: true },
    );
  }

  async getProfile(userId: string) {
    const user = await this.userRepository.findOne({
      where: { id: userId },
    });
    if (!user) {
      throw new NotFoundException('User not found');
    }
    return { id: user.id, email: user.email, createdAt: user.createdAt };
  }

  async getSessions(userId: string) {
    const sessions = await this.refreshTokenRepository.find({
      where: { userId, revoked: false },
      select: ['id', 'userAgent', 'createdAt', 'expiresAt'],
      order: { createdAt: 'DESC' },
    });
    return sessions;
  }

  decodeRefreshToken(token: string) {
    return this.jwtService.decode(token);
  }

  setRefreshTokenCookie(res: Response, refreshToken: string) {
    const isProduction = this.configService.get('NODE_ENV') === 'production';
    res.cookie('refresh_token', refreshToken, {
      httpOnly: true,
      secure: isProduction,
      sameSite: 'strict',
      path: '/auth',
      maxAge: 7 * 24 * 60 * 60 * 1000,
    });
  }

  private signAccessToken(userId: string, email: string): string {
    const secret = this.configService.get<string>('JWT_ACCESS_SECRET')!;
    const expiresIn = this.configService.get<string>('JWT_ACCESS_EXPIRES_IN', '15m') as any;
    return this.jwtService.sign(
      { sub: userId, email },
      { secret, expiresIn },
    );
  }

  private signRefreshToken(userId: string, sessionId: string): string {
    const secret = this.configService.get<string>('JWT_REFRESH_SECRET')!;
    const expiresIn = this.configService.get<string>('JWT_REFRESH_EXPIRES_IN', '7d') as any;
    return this.jwtService.sign(
      { sub: userId, sessionId },
      { secret, expiresIn },
    );
  }

  private async issueTokenPair(
    userId: string,
    email: string,
    req: Request,
  ): Promise<{ accessToken: string; refreshToken: string }> {
    const accessToken = this.signAccessToken(userId, email);

    // Create session first to get the sessionId
    const userAgent = req.headers['user-agent'] || null;
    const session = await this.refreshTokenRepository.save(
      this.refreshTokenRepository.create({
        userId,
        hashedToken: 'pending',
        userAgent,
        expiresAt: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000),
      }),
    );

    const refreshToken = this.signRefreshToken(userId, session.id);

    // Update with the real hashed token
    session.hashedToken = await argon2.hash(refreshToken);
    await this.refreshTokenRepository.save(session);

    return { accessToken, refreshToken };
  }

  private async revokeAllUserSessions(userId: string) {
    await this.refreshTokenRepository.update(
      { userId, revoked: false },
      { revoked: true },
    );
  }
}
