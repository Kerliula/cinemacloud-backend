import prisma from '../config/database.ts';
import ROLES from '../constants/roles.ts';
import {
  type CreateUserInput,
  type UserWithRelations,
} from '../types/user.types.ts';

export class UserModel {
  static async create(data: CreateUserInput): Promise<UserWithRelations> {
    const defaultRole = ROLES.find(role => role.default);
    if (!defaultRole) {
      throw new Error('no default role configured');
    }

    return prisma.user.create({
      data: {
        email: data.email,
        password: data.password,
        username: data.username,
        roles: {
          connect: { name: defaultRole.name },
        },
      },
      include: { roles: true },
    });
  }

  static async findByEmail(email: string): Promise<UserWithRelations | null> {
    return prisma.user.findUnique({
      where: { email },
      include: { roles: true },
    });
  }

  static toPublic(
    user: UserWithRelations
  ): Omit<UserWithRelations, 'password'> {
    const { password: _password, ...publicUser } = user as UserWithRelations & {
      password: string;
    };
    return publicUser;
  }
}
