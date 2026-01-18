import prisma from '../config/database.ts';
import type { User } from '../generated/prisma/client.ts';

export class UserModel {
  private static readonly SALT_ROUNDS = 10;

  static async create(data: {
    email: string;
    hashedPassword: string;
    username: string;
  }): Promise<User> {
    return prisma.user.create({
      data: {
        email: data.email,
        password: data.hashedPassword,
        username: data.username,
      },
    });
  }

  static async findByEmail(email: string): Promise<User | null> {
    return prisma.user.findUnique({
      where: { email },
    });
  }

  static toPublic(user: User) {
    const { password, ...publicUser } = user;
    return publicUser;
  }
}
