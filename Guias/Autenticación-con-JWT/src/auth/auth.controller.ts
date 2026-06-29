import {
  Controller,
  Post,
  Get,
  Body,
  Req,
  Res,
  UseGuards,
  HttpCode,
  HttpStatus,
} from '@nestjs/common';
import { Request, Response } from 'express';
import { AuthService } from './auth.service';
import { RegisterDto } from './dto/register.dto';
import { LoginDto } from './dto/login.dto';
import { AccessJwtGuard } from './guards/access-jwt.guard';
import { RefreshJwtGuard } from './guards/refresh-jwt.guard';

@Controller('auth')
export class AuthController {
  constructor(private readonly authService: AuthService) {}

  @Post('register')
  async register(
    @Body() registerDto: RegisterDto,
    @Req() req: Request,
    @Res({ passthrough: true }) res: Response,
  ) {
    return this.authService.register(registerDto, req, res);
  }

  @Post('login')
  @HttpCode(HttpStatus.OK)
  async login(
    @Body() loginDto: LoginDto,
    @Req() req: Request,
    @Res({ passthrough: true }) res: Response,
  ) {
    return this.authService.login(loginDto, req, res);
  }

  @UseGuards(RefreshJwtGuard)
  @Post('refresh')
  @HttpCode(HttpStatus.OK)
  async refresh(
    @Req() req: any,
    @Res({ passthrough: true }) res: Response,
  ) {
    const { sub, sessionId, refreshToken } = req.user;
    const result = await this.authService.refresh(sub, sessionId, refreshToken);
    this.authService.setRefreshTokenCookie(res, result.refreshToken);
    return { accessToken: result.accessToken };
  }

  @UseGuards(AccessJwtGuard)
  @Get('me')
  getProfile(@Req() req: any) {
    return this.authService.getProfile(req.user.sub);
  }

  @UseGuards(AccessJwtGuard)
  @Post('logout')
  @HttpCode(HttpStatus.OK)
  async logout(
    @Req() req: any,
    @Res({ passthrough: true }) res: Response,
  ) {
    const refreshToken = req.cookies?.refresh_token;
    if (refreshToken) {
      try {
        const payload = this.authService.decodeRefreshToken(refreshToken);
        if (payload?.sessionId) {
          await this.authService.logout(payload.sessionId);
        }
      } catch {
        // Token might already be invalid
      }
    }
    res.clearCookie('refresh_token', { path: '/auth' });
    return { message: 'Logged out successfully' };
  }

  @UseGuards(AccessJwtGuard)
  @Get('sessions')
  getSessions(@Req() req: any) {
    return this.authService.getSessions(req.user.sub);
  }
}
