import { type UserEntity, type UserData } from './index.ts';

export interface UserEntityFactory {
  create(data: UserData): UserEntity;
}
