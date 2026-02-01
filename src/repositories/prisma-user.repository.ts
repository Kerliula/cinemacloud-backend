import { PrismaClient } from '@prisma/client';
import { EntityNotFoundError } from '../errors/index.ts';
import { type UserRepository } from '../interfaces/user.repository.interface.ts';
import { type UserWithRelations } from '../types/user.types.ts';

export class PrismaUserRepository implements UserRepository {
  private readonly USER_INCLUDES = {
    roles: true,
  } as const;

  private readonly db: PrismaClient;

  constructor(db: PrismaClient) {
    this.db = db;
  }

  public async findByEmail(email: string): Promise<UserWithRelations | null> {
    return this.db.user.findUnique({
      where: { email },
      include: this.USER_INCLUDES,
    });
  }

  public async findById(id: number): Promise<UserWithRelations | null> {
    return this.db.user.findUnique({
      where: { id },
      include: this.USER_INCLUDES,
    });
  }

  public async findByEmailOrFail(email: string): Promise<UserWithRelations> {
    const user = await this.findByEmail(email);
    // Passing email directly to EntityNotFoundError may result in user email addresses appearing in logs,
    // which is a privacy/compliance concern (GDPR, CCPA).
    if (!user) throw new EntityNotFoundError('User', 'email');
    return user;
  }

  public async findByIdOrFail(id: number): Promise<UserWithRelations> {
    const user = await this.findById(id);
    if (!user) throw new EntityNotFoundError('User', id);
    return user;
  }
}
