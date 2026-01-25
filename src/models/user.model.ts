import type { User } from '@prisma/client';

import prisma from '../config/database.ts';
import ROLES from '../constants/roles.ts';
import {
  type CreateUserInput,
  type UpdateUserInput,
  type UserWithRelations,
} from '../types/user.types.ts';

export class UserModel {
  public static async create(
    data: CreateUserInput
  ): Promise<UserWithRelations> {
    const defaultRole = ROLES.find(role => role.default);

    // Move to repository?
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

  public static async update(
    id: User['id'],
    data: UpdateUserInput
  ): Promise<UserWithRelations> {
    return prisma.user.update({
      where: { id },
      data,
      include: { roles: true },
    });
  }

  public static async isAccountStillLocked(
    userId: User['id']
  ): Promise<boolean> {
    const locked = await prisma.user.findFirst({
      where: {
        id: userId,
        accountLockedUntil: { gt: new Date() },
      },
      select: { id: true },
    });
    return !!locked;
  }

  public static async removeAccountLock(userId: User['id']): Promise<void> {
    await prisma.user.update({
      where: { id: userId },
      data: { accountLockedUntil: null },
    });
  }

  public static async resetFailedAttempts(userId: User['id']): Promise<void> {
    await prisma.user.update({
      where: { id: userId },
      data: { failedLoginAttempts: 0 },
    });
  }

  public static async markLogin(userId: User['id']): Promise<void> {
    await prisma.user.update({
      where: { id: userId },
      data: {
        lastLoginAt: new Date(),
      },
    });
  }

  public static async incrementFailedAttempts(
    userId: User['id']
  ): Promise<void> {
    await prisma.user.update({
      where: { id: userId },
      data: { failedLoginAttempts: { increment: 1 } },
    });
  }

  public static async lockAccount(
    userId: User['id'],
    durationMs: number
  ): Promise<void> {
    await prisma.user.update({
      where: { id: userId },
      data: { accountLockedUntil: new Date(Date.now() + durationMs) },
    });
  }

  public static async findByEmail(
    email: string
  ): Promise<UserWithRelations | null> {
    return prisma.user.findUnique({
      where: { email },
      include: { roles: true },
    });
  }

  public static async findById(
    id: User['id']
  ): Promise<UserWithRelations | null> {
    return prisma.user.findUnique({
      where: { id },
      include: { roles: true },
    });
  }

  public static toPublic(
    user: UserWithRelations
  ): Omit<UserWithRelations, 'password'> {
    const { password: _password, ...publicUser } = user as UserWithRelations & {
      password: string;
    };
    return publicUser;
  }
}
