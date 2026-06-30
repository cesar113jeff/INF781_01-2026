import { Injectable } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { PassportStrategy } from '@nestjs/passport';
import {
  ExtractJwt,
  Strategy,
  StrategyOptionsWithRequest,
} from 'passport-jwt';
import { Request } from 'express';

type Payload = { sub: string; email: string; sessionId: string };

const fromCookie = (req: Request): string | null =>
  req?.cookies?.refreshToken ?? null;

@Injectable()
export class RefreshTokenStrategy extends PassportStrategy(
  Strategy,
  'jwt-refresh',
) {
  constructor(config: ConfigService) {
    const opts: StrategyOptionsWithRequest = {
      jwtFromRequest: ExtractJwt.fromExtractors([fromCookie]),
      secretOrKey: config.get<string>('JWT_REFRESH_SECRET')!,
      passReqToCallback: true,
    };
    super(opts);
  }

  validate(req: Request, payload: Payload) {
    return { ...payload, refreshToken: req?.cookies?.refreshToken };
  }
}
