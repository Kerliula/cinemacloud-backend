import { PrismaClient } from '@prisma/client';

import ROLES from '../src/constants/roles.ts';

const prisma = new PrismaClient();

async function main() {
  for (const { name } of ROLES) {
    await prisma.role.upsert({
      where: { name },
      update: {},
      create: { name },
    });
  }
}

main()
  .catch(e => {
    console.error(e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
