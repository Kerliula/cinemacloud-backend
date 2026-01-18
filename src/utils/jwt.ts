import { randomBytes } from 'crypto';

import jwt, { type SignOptions } from 'jsonwebtoken';

class jwtUtils {
  static generateToken(
    payload: object,
    secret: string,
    expiresIn?: string | number,
    options?: Partial<SignOptions>
  ): string {
    const jti = randomBytes(16).toString('hex');

    const signOptions: SignOptions = {
      expiresIn: expiresIn as jwt.SignOptions['expiresIn'],
      ...options,
    };

    return jwt.sign({ ...payload, jti }, secret, signOptions);
  }

  static verifyToken<T>(dtoken: string, secret: string): T {
    return jwt.verify(dtoken, secret) as T;
  }

  static decodeToken<T>(dtoken: string): T | null {
    return jwt.decode(dtoken) as T | null;
  }
}

export default jwtUtils;
