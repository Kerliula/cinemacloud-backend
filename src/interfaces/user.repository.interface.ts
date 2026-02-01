import { type CreateUserData, type UserEntity } from './user-entity.interface.ts';

export interface UserRepository {
  // Queries
  findByEmail(email: string): Promise<UserEntity | null>;
  findById(id: number): Promise<UserEntity | null>;
  update(user: UserEntity): Promise<UserEntity>;
  create(data: CreateUserData): Promise<UserEntity>;

  // Queries that throw (OrFail)
  findByEmailOrFail(email: string): Promise<UserEntity>;
  findByIdOrFail(id: number): Promise<UserEntity>;
}
