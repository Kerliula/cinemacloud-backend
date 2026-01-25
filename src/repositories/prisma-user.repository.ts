import { UserNotFoundError } from '../errors/index.ts';
import { type UserRepository } from '../interfaces/user.repository.interface.ts';
import { UserModel } from '../models/user.model.ts';
import { type UserWithRelations } from '../types/user.types.ts';

export class PrismaUserRepository implements UserRepository {
  public async findByEmail(email: string): Promise<UserWithRelations> {
    const user = await UserModel.findByEmail(email);

    if (!user) {
      throw new UserNotFoundError();
    }

    return user;
  }

  public async findById(id: number): Promise<UserWithRelations> {
    const user = await UserModel.findById(id);

    if (!user) {
      throw new UserNotFoundError();
    }
    return user;
  }
}

export const prismaUserRepository = new PrismaUserRepository();
