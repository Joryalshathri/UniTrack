import psycopg2

def setup_database():
    try:
        # Connect to postgres database
        conn = psycopg2.connect(
            host='localhost',
            user='postgres',
            password='8611622',
            dbname='postgres'
        )
        conn.autocommit = True
        cur = conn.cursor()
        
        # Drop and recreate database
        try:
            cur.execute("DROP DATABASE IF EXISTS students_management_db;")
            print("✅ Dropped old database")
        except:
            pass
        
        cur.execute("CREATE DATABASE students_management_db;")
        print("✅ Created database: students_management_db")
        
        conn.close()
        
        # Connect to the new database
        conn = psycopg2.connect(
            host='localhost',
            user='postgres',
            password='8611622',
            dbname='students_management_db'
        )
        conn.autocommit = True
        cur = conn.cursor()
        
        # Create enums
        cur.execute("""CREATE TYPE user_role AS ENUM ('admin', 'teacher', 'student');""")
        print("✅ Created user_role type")
        
        cur.execute("""CREATE TYPE attendance_status AS ENUM ('present', 'absent', 'late');""")
        print("✅ Created attendance_status type")
        
        # Create tables
        cur.execute("""
            CREATE TABLE users (
                user_id SERIAL PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                email VARCHAR(100) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role user_role NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        """)
        print("✅ Created users table")
        
        cur.execute("""
            CREATE TABLE students (
                student_id SERIAL PRIMARY KEY,
                user_id INT UNIQUE,
                enrollment_number VARCHAR(20) NOT NULL UNIQUE,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                phone_number VARCHAR(20),
                date_of_birth DATE,
                enrollment_date DATE DEFAULT CURRENT_DATE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
            );
        """)
        print("✅ Created students table")
        
        cur.execute("""
            CREATE TABLE attendance (
                attendance_id SERIAL PRIMARY KEY,
                student_id INT NOT NULL,
                attendance_date DATE NOT NULL,
                status attendance_status NOT NULL,
                marked_by INT NOT NULL,
                remarks VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
                FOREIGN KEY (marked_by) REFERENCES users(user_id) ON DELETE RESTRICT,
                UNIQUE (student_id, attendance_date)
            );
        """)
        print("✅ Created attendance table")
        
        # Insert sample data
        cur.execute("""
            INSERT INTO users (username, email, password_hash, role, first_name, last_name)
            VALUES 
                ('admin', 'admin@unitrack.edu', '$2b$10$O/KM3xBFvfmjaKAtS2wmiucw7.AUSaZhWkqGGrml4sauqFow55/36', 'admin', 'Admin', 'System'),
                ('teacher_amal', 'amal@unitrack.edu', '$2b$10$O/KM3xBFvfmjaKAtS2wmiucw7.AUSaZhWkqGGrml4sauqFow55/36', 'teacher', 'أمل', 'الهاجري');
        """)
        print("✅ Inserted users")
        
        cur.execute("""
            INSERT INTO students (enrollment_number, first_name, last_name, email, phone_number, date_of_birth)
            VALUES 
                ('STU001', 'ليلى', 'الحسن', 'layla@student.edu', '5551234567', '2000-01-15'),
                ('STU002', 'فاطمة', 'محمود', 'fatima@student.edu', '5552345678', '2000-03-22'),
                ('STU003', 'هند', 'أحمد', 'hind@student.edu', '5553456789', '2001-05-10'),
                ('STU004', 'نور', 'علي', 'noor@student.edu', '5554567890', '2000-07-30'),
                ('STU005', 'سارة', 'كريم', 'sara@student.edu', '5555678901', '2001-09-12');
        """)
        print("✅ Inserted students")
        
        cur.execute("""
            INSERT INTO attendance (student_id, attendance_date, status, marked_by, remarks)
            VALUES 
                (1, CURRENT_DATE - INTERVAL '1 day', 'present', 1, 'Regular attendance'),
                (2, CURRENT_DATE - INTERVAL '1 day', 'present', 1, NULL),
                (3, CURRENT_DATE - INTERVAL '1 day', 'absent', 1, 'Sick leave'),
                (4, CURRENT_DATE - INTERVAL '1 day', 'late', 1, '15 mins late'),
                (5, CURRENT_DATE - INTERVAL '1 day', 'present', 1, NULL);
        """)
        print("✅ Inserted attendance records")
        
        conn.commit()
        conn.close()
        print(f'\n✅ Database setup complete!')
        
    except Exception as e:
        print(f'❌ Error: {e}')

if __name__ == '__main__':
    setup_database()
