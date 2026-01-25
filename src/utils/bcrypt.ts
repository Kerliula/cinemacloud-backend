import bcrypt from 'bcrypt';

const HASH_SALT_ROUNDS = 10;

class bcryptUtils {
  static async hash(
    password: string,
    saltRounds: number = HASH_SALT_ROUNDS
  ): Promise<string> {
    if (!password || password.length === 0) {
      throw new Error('Password cannot be empty');
    }

    return bcrypt.hash(password, saltRounds);
  }
  static async compare(
    password: string,
    hashedPassword: string
  ): Promise<boolean> {
    return bcrypt.compare(password, hashedPassword);
  }
}

export default bcryptUtils;
