const ROLES = [
  {
    name: 'User',
    default: true,
  },
  {
    name: 'Admin',
    default: false,
  },
] as const;

export default ROLES;
