import { LocalUserEntity } from '../entities/local-user.entity.ts';
import { type UserEntityFactory, type UserData } from '../interfaces/index.ts';

export class LocalUserFactory implements UserEntityFactory {
  public create(data: UserData): LocalUserEntity {
    return new LocalUserEntity(data);
  }
}
