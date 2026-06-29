import { Injectable } from '@nestjs/common';
import { JwtService } from '@nestjs/jwt';
import { UserService } from '../users/user.service';
import { ConfigService } from '@nestjs/config';
import { RegisterDto } from '../users/register.dto';
import { ForbiddenException } from '@nestjs/common/exceptions';
import * as argon from 'argon2';

@Injectable()
export class AuthService {
    constructor(
        private readonly userService: UserService,
        private readonly jwt: JwtService,
        private readonly config: ConfigService
    ) {}

    async register(dto: RegisterDto) {
        const existingUser = await this.userService.findByEmail(dto.email);
        if (existingUser) {
            throw new ForbiddenException('el usuario esta registrado');
        }
        const hash = await argon.hash(dto.password);
        const user = await this.userService.create(dto.email, hash);
        return this.getToken(user.id, user.email);
    }
    private async getToken(sub:string, email:string){

        }
    }
}