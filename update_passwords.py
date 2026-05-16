import psycopg2

conn = psycopg2.connect(
    host='localhost',
    user='postgres',
    password='8611622',
    dbname='students_management_db'
)
cur = conn.cursor()

# Update admin password
cur.execute(
    "UPDATE users SET password_hash = %s WHERE username = 'admin'",
    ('$2b$10$PyT1MVQRCZj4EzR7d/bV2emcC185UqimUC66394X3.pPZG1ZbCqxK',)
)

# Also update teacher password
cur.execute(
    "UPDATE users SET password_hash = %s WHERE username = 'teacher_john'",
    ('$2b$10$PyT1MVQRCZj4EzR7d/bV2emcC185UqimUC66394X3.pPZG1ZbCqxK',)
)

conn.commit()
conn.close()
print('✅ Updated password hashes for demo users')
