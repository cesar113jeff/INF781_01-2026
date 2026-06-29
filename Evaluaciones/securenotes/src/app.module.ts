import { Module } from '@nestjs/common';
import { ConfigModule, ConfigService } from '@nestjs/config';
import { TypeOrmModule } from '@nestjs/typeorm';
import { AuthModule } from './auth/auth.module';
import { NotesModule } from './notes/notes.module';
import { UsersModule } from './users/users.module';
import { User } from './users/entities/user.entity';
import { Note } from './entities/note.entity';
import { RefreshToken } from './entities/refresh-token.entity';

@Module({
  imports: [
    ConfigModule.forRoot({
      isGlobal: true,
    }),
    TypeOrmModule.forRootAsync({
      imports: [ConfigModule],
      useFactory: (configService: ConfigService) => ({
        type: 'postgres',
        host: configService.get<string>('DB_HOST', 'localhost'),
        port: configService.get<number>('DB_PORT', 5432),
        username: configService.get<string>('DB_USERNAME', 'postgres'),
        password: configService.get<string>('DB_PASSWORD', ''),
        database: configService.get<string>('DB_NAME', 'securenotes'),
        entities: [User, Note, RefreshToken],
        synchronize:
          configService.get<string>('NODE_ENV') !== 'production',
        autoLoadEntities: false,
        connectTimeoutMS: 5000,
      }),
      inject: [ConfigService],
    }),
    AuthModule,
    NotesModule,
    UsersModule,
  ],
})
export class AppModule {}
