import { Injectable, UnauthorizedException } from '@nestjs/common';
import { PassportStrategy } from '@nestjs/passport';
import { ExtractJwt, Strategy } from 'passport-jwt';
import { ConfigService } from '@nestjs/config';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Request } from 'express';
import { RefreshToken } from '../../entities/refresh-token.entity';

export interface RefreshJwtPayload {
  sub: string;
  sessionId: string;
}

@Injectable()
export class RefreshJwtStrategy extends PassportStrategy(
  Strategy,
  'refresh-jwt',
) {
  constructor(
    configService: ConfigService,
    @InjectRepository(RefreshToken)
    private readonly refreshTokenRepository: Repository<RefreshToken>,
  ) {
    const secret = configService.get<string>('JWT_REFRESH_SECRET');
    super({
      jwtFromRequest: ExtractJwt.fromExtractors([
        (request: Request) => {
          return request?.cookies?.refresh_token;
        },
      ]),
      ignoreExpiration: false,
      secretOrKey: secret!,
      passReqToCallback: true,
    });
  }

  async validate(req: Request, payload: RefreshJwtPayload) {
    const refreshToken = req.cookies?.refresh_token;
    if (!refreshToken) {
      throw new UnauthorizedException('Refresh token not found');
    }

    const session = await this.refreshTokenRepository.findOne({
      where: { id: payload.sessionId, userId: payload.sub, revoked: false },
    });

    if (!session) {
      throw new UnauthorizedException('Session revoked or not found');
    }

    return {
      sub: payload.sub,
      sessionId: payload.sessionId,
      refreshToken,
      session,
    };
  }
}
