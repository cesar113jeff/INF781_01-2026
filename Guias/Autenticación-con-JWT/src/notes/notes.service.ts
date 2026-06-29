import { Injectable, NotFoundException } from '@nestjs/common';
import { InjectRepository } from '@nestjs/typeorm';
import { Repository } from 'typeorm';
import { Note } from '../entities/note.entity';
import { CreateNoteDto } from './dto/create-note.dto';
import { UpdateNoteDto } from './dto/update-note.dto';

@Injectable()
export class NotesService {
  constructor(
    @InjectRepository(Note)
    private readonly noteRepository: Repository<Note>,
  ) {}

  async create(createNoteDto: CreateNoteDto, userId: string) {
    const note = this.noteRepository.create({
      title: createNoteDto.title,
      content: createNoteDto.content,
      ownerId: userId,
    });
    return this.noteRepository.save(note);
  }

  async findAllByUser(userId: string) {
    return this.noteRepository.find({
      where: { ownerId: userId },
      order: { createdAt: 'DESC' },
    });
  }

  async findOneByUser(id: string, userId: string) {
    const note = await this.noteRepository.findOne({
      where: { id, ownerId: userId },
    });
    if (!note) {
      throw new NotFoundException('Note not found');
    }
    return note;
  }

  async update(id: string, updateNoteDto: UpdateNoteDto, userId: string) {
    const note = await this.findOneByUser(id, userId);
    Object.assign(note, updateNoteDto);
    return this.noteRepository.save(note);
  }

  async remove(id: string, userId: string) {
    const note = await this.findOneByUser(id, userId);
    await this.noteRepository.remove(note);
    return { message: 'Note deleted successfully' };
  }
}
