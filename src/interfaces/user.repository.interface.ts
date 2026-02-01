import { type UserWithRelations } from '../types/user.types.ts';

export interface UserRepository {
  // Queries
  findByEmail(email: string): Promise<UserWithRelations | null>;
  findById(id: number): Promise<UserWithRelations | null>;
  
  // Queries that throw (OrFail)
  findByEmailOrFail(email: string): Promise<UserWithRelations>;
  findByIdOrFail(id: number): Promise<UserWithRelations>;
}