import {
  Controller,
  Get,
  Post,
  Patch,
  Delete,
  Body,
  Param,
  Req,
  UseGuards,
  HttpCode,
  HttpStatus,
  ParseUUIDPipe,
} from '@nestjs/common';
import { NotesService } from './notes.service';
import { CreateNoteDto } from './dto/create-note.dto';
import { UpdateNoteDto } from './dto/update-note.dto';
import { AccessJwtGuard } from '../auth/guards/access-jwt.guard';

@UseGuards(AccessJwtGuard)
@Controller('notes')
export class NotesController {
  constructor(private readonly notesService: NotesService) {}

  @Post()
  @HttpCode(HttpStatus.CREATED)
  create(@Body() createNoteDto: CreateNoteDto, @Req() req: any) {
    return this.notesService.create(createNoteDto, req.user.sub);
  }

  @Get()
  findAll(@Req() req: any) {
    return this.notesService.findAllByUser(req.user.sub);
  }

  @Get(':id')
  findOne(@Param('id', ParseUUIDPipe) id: string, @Req() req: any) {
    return this.notesService.findOneByUser(id, req.user.sub);
  }

  @Patch(':id')
  update(
    @Param('id', ParseUUIDPipe) id: string,
    @Body() updateNoteDto: UpdateNoteDto,
    @Req() req: any,
  ) {
    return this.notesService.update(id, updateNoteDto, req.user.sub);
  }

  @Delete(':id')
  @HttpCode(HttpStatus.OK)
  remove(@Param('id', ParseUUIDPipe) id: string, @Req() req: any) {
    return this.notesService.remove(id, req.user.sub);
  }
}
