// Initialize central database and create application user
db = db.getSiblingDB('ecommerce_central');

db.createUser({
  user: 'ecommerce_user',
  pwd: 'ecommerce_pass',
  roles: [
    { role: 'readWrite', db: 'ecommerce_central' },
    { role: 'dbAdminAnyDatabase', db: 'admin' },
    { role: 'readWriteAnyDatabase', db: 'admin' }
  ]
});

// Create tenants collection with index
db.tenants.createIndex({ domain: 1 }, { unique: true });
db.tenants.createIndex({ slug: 1 }, { unique: true });

print('MongoDB initialized successfully');
