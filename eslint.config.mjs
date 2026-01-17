import eslint from '@eslint/js';
import pluginImport from 'eslint-plugin-import';
import pluginN from 'eslint-plugin-n';
import pluginPromise from 'eslint-plugin-promise';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  eslint.configs.recommended,
  ...tseslint.configs.recommended,
  pluginPromise.configs['flat/recommended'],
  {
    plugins: {
      import: pluginImport,
      n: pluginN,
    },
    rules: {
      // TypeScript-specific
      '@typescript-eslint/no-unused-vars': ['warn', { 
        argsIgnorePattern: '^_',
        varsIgnorePattern: '^_' 
      }],
      '@typescript-eslint/no-explicit-any': 'warn',
      '@typescript-eslint/explicit-function-return-type': 'off',
      '@typescript-eslint/explicit-module-boundary-types': 'off',
      '@typescript-eslint/no-non-null-assertion': 'warn',
      '@typescript-eslint/consistent-type-imports': ['warn', {
        prefer: 'type-imports',
        fixStyle: 'inline-type-imports'
      }],

      // General code quality
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      'no-debugger': 'warn',
      'prefer-const': 'error',
      'no-var': 'error',
      'eqeqeq': ['error', 'always'],
      'curly': ['error', 'all'],
      'no-throw-literal': 'error',
      
      // Import/Export
      'import/order': ['warn', {
        'groups': [
          'builtin',
          'external',
          'internal',
          'parent',
          'sibling',
          'index'
        ],
        'newlines-between': 'always',
        'alphabetize': { order: 'asc' }
      }],
      'import/no-duplicates': 'error',
      'import/newline-after-import': 'warn',
      
      // Node.js specific
      'n/no-unsupported-features/es-syntax': 'off', // TypeScript handles this
      'n/no-missing-import': 'off', // TypeScript handles this
      'n/no-process-exit': 'warn',
      
      // Promises
      'promise/always-return': 'off',
      'promise/catch-or-return': 'warn',
    },
  },
  {
    ignores: [
      'dist/',
      'build/',
      'node_modules/',
      '*.js',
      'coverage/',
      '.env*',
    ],
  }
);