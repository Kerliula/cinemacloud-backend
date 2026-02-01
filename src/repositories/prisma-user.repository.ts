import {
  type PrismaClient,
  type User as PrismaUser,
  type Role,
} from '@prisma/client';

import { EntityNotFoundError } from '../errors/index.ts';
import {
  type UserRepository,
  type UserEntityFactory,
  type UserEntity,
  type CreateUserData,
} from '../interfaces/index.ts';

type UserWithRoles = PrismaUser & { roles: Role[] };

export class PrismaUserRepository implements UserRepository {
  private readonly USER_INCLUDES = { roles: true } as const;

  constructor(
    private readonly db: PrismaClient,
    private readonly userEntityFactory: UserEntityFactory
  ) {}

  public async findByEmail(email: string): Promise<UserEntity | null> {
    const userData = await this.db.user.findUnique({
      where: { email },
      include: this.USER_INCLUDES,
    });

    return userData
      ? this.userEntityFactory.create(userData as UserWithRoles)
      : null;
  }

  public async findById(id: number): Promise<UserEntity | null> {
    const userData = await this.db.user.findUnique({
      where: { id },
      include: this.USER_INCLUDES,
    });

    return userData
      ? this.userEntityFactory.create(userData as UserWithRoles)
      : null;
  }

  public async findByEmailOrFail(email: string): Promise<UserEntity> {
    const user = await this.findByEmail(email);
    // email is not provided to entitynotfounderror, as it violates privacy best practices
    if (!user) throw new EntityNotFoundError('User', 'email provided');
    return user;
  }

  public async findByIdOrFail(id: number): Promise<UserEntity> {
    const user = await this.findById(id);
    if (!user) throw new EntityNotFoundError('User', id);
    return user;
  }

  // Prisma create throws if not found, so no extra checks needed.
  public async create(data: CreateUserData): Promise<UserEntity> {
    const createdData = await this.db.user.create({
      data: {
        email: data.email,
        password: data.passwordHash,
        username: data.username,
        roles: {
          connect: { id: data.roleId },
        },
      },
      include: this.USER_INCLUDES,
    });

    return this.userEntityFactory.create(createdData as UserWithRoles);
  }

  // Prisma update throws if not found, so no extra checks needed.
  public async update(user: UserEntity): Promise<UserEntity> {
    const { id, roles, ...scalarData } = user.data;

    const updatedData = await this.db.user.update({
      where: { id },
      data: {
        ...scalarData,
        roles: {
          set: [],
          connect: roles.map(role => ({ id: role.id })),
        },
      },
      include: this.USER_INCLUDES,
    });

    return this.userEntityFactory.create(updatedData as UserWithRoles);
  }
}
