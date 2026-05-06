# 📊 Programming Paradigm Comparison: Python vs PHP

## CS 516 Advanced Programming Language Course  
**Students' Management & Attendance System**  
**Submitted:** May 6, 2026

---

## 1. SYNTAX COMPARISON

### 1.1 Variable Declaration & Typing

**Python:**
```python
# Dynamic typing - type inferred at runtime
username: str = "admin123"  # Type hint (optional)
password_hash = bcrypt.hashpw(b"password", bcrypt.gensalt(10))
students: list[dict] = []

# Function definition with type hints
def validate_email(email: str) -> bool:
    return "@" in email and "." in email
```

**PHP:**
```php
// Weakly typed - automatic type conversion
$username = "admin123";  // No type declaration needed
$password_hash = password_hash("password", PASSWORD_BCRYPT, ['cost' => 10]);
$students = [];  // Empty array

// Function definition - no type hints (PHP 5.4 used)
function validate_email($email) {
    return strpos($email, "@") !== false && strpos($email, ".") !== false;
}
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Type System** | Strong, dynamic (compile-time checking possible) | Weak, dynamic (automatic conversion) |
| **Type Hints** | Native support (Python 3.5+) | No type hints (PHP 5.4; available in 7.0+) |
| **Variable Prefix** | No prefix required | `$` prefix mandatory |
| **String Handling** | Single/double quotes equivalent | Double quotes for interpolation |

---

### 1.2 Database Queries & SQL Injection Prevention

**Python (Flask with psycopg2):**
```python
# Parameterized queries with %s placeholders
def get_student_by_enrollment(enrollment_number):
    cursor.execute(
        "SELECT user_id, first_name, last_name FROM users WHERE enrollment_number = %s",
        (enrollment_number,)  # Tuple parameter
    )
    return cursor.fetchone()

# INSERT with parameters
cursor.execute(
    """INSERT INTO users (username, password_hash, role, first_name, last_name) 
       VALUES (%s, %s, %s, %s, %s)""",
    (username, hash_value, 'student', first_name, last_name)
)
```

**PHP (pg_query_params):**
```php
// Parameterized queries with $1, $2, $3... placeholders
$result = pg_query_params(
    $connection,
    "SELECT user_id, first_name, last_name FROM users WHERE enrollment_number = $1",
    array($enrollment_number)  // Array parameter
);

// INSERT with parameters
pg_query_params(
    $connection,
    "INSERT INTO users (username, password_hash, role, first_name, last_name) 
     VALUES ($1, $2, $3, $4, $5)",
    array($username, $hash_value, 'student', $first_name, $last_name)
);
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Placeholder Style** | `%s` for all types | `$1, $2, $3...` positional |
| **Parameter Format** | Tuple `()` | Array `[]` |
| **Execute Method** | `cursor.execute()` | `pg_query_params()` |
| **Return Type** | Cursor/Row object | PostgreSQL result resource |
| **SQL Injection Prevention** | ✅ Native support | ✅ Native support |

---

### 1.3 Authentication & Password Hashing

**Python:**
```python
from werkzeug.security import generate_password_hash, check_password_hash
import bcrypt

# Option 1: Werkzeug (used in Flask-User)
hashed = generate_password_hash(password, method='bcrypt', rounds=10)
if check_password_hash(hashed, password):
    print("Password matches!")

# Option 2: Direct bcrypt
hashed = bcrypt.hashpw(password.encode(), bcrypt.gensalt(10))
if bcrypt.checkpw(password.encode(), hashed):
    print("Password matches!")
```

**PHP:**
```php
// PHP built-in password functions
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

// Verify password
if (password_verify($password, $hashed)) {
    echo "Password matches!";
}

// Check if password needs rehashing (for future improvement)
if (password_needs_rehash($hashed, PASSWORD_BCRYPT)) {
    $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Library** | bcrypt/werkzeug (external) | Built-in password_* functions |
| **Hash Function** | `generate_password_hash()` | `password_hash()` |
| **Verification** | `check_password_hash()` | `password_verify()` |
| **Cost/Rounds** | 10 (standard) | 10 (standard) |
| **Binary Handling** | Requires `.encode()` for bytes | Automatic string handling |

---

### 1.4 Session Management & HTTP Headers

**Python (Flask-Session):**
```python
from flask import session

# Create session
@app.route('/auth/login', methods=['POST'])
def login():
    # Set session data
    session['user_id'] = user['user_id']
    session['username'] = user['username']
    session['login_time'] = datetime.now()
    
    # Session auto-saved in response headers
    return redirect('/dashboard')

# Access session
@app.before_request
def require_login():
    if 'user_id' not in session:
        return redirect('/auth/login')

# Destroy session
@app.route('/auth/logout')
def logout():
    session.clear()
    return redirect('/auth/login')
```

**PHP (Built-in Sessions):**
```php
// Start session
session_start();

// Create session
if (password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['login_time'] = time();
    
    header('Location: ' . BASE_URL . '?action=dashboard');
    exit;
}

// Access session
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '?action=login');
    exit;
}

// Destroy session
session_destroy();
unset($_SESSION);
header('Location: ' . BASE_URL . '?action=login');
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Initialization** | Automatic via Flask | `session_start()` required |
| **Storage** | Server-side (configurable) | File-based by default |
| **Access Method** | `session[]` dict | `$_SESSION[]` superglobal |
| **Timeout** | Flask-Session configurable | php.ini `session.gc_maxlifetime` |
| **Cleanup** | `session.clear()` | `session_destroy()` |

---

### 1.5 Template Rendering & HTML Integration

**Python (Jinja2):**
```python
from flask import render_template

@app.route('/students/<id>')
def student_detail(id):
    student = get_student_by_id(id)
    return render_template('students/detail.html', student=student)
```

```html
<!-- Jinja2 template syntax -->
<h1>{{ student.first_name }} {{ student.last_name }}</h1>
<p>Enrollment: <strong>{{ student.enrollment_number }}</strong></p>

{% if student.is_active %}
    <span class="badge bg-success">Active</span>
{% else %}
    <span class="badge bg-danger">Inactive</span>
{% endif %}

<table class="table">
    {% for record in attendance_records %}
        <tr>
            <td>{{ record.attendance_date | strftime('%Y-%m-%d') }}</td>
            <td>{{ record.status | title }}</td>
        </tr>
    {% endfor %}
</table>
```

**PHP:**
```php
// Mixed PHP/HTML
include 'templates/header.php';

$student = get_student_by_id($student_id);
?>

<h1><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>
<p>Enrollment: <strong><?php echo htmlspecialchars($student['enrollment_number']); ?></strong></p>

<?php if ($student['is_active']): ?>
    <span class="badge bg-success">Active</span>
<?php else: ?>
    <span class="badge bg-danger">Inactive</span>
<?php endif; ?>

<table class="table">
    <?php foreach ($attendance_records as $record): ?>
        <tr>
            <td><?php echo date('Y-m-d', strtotime($record['attendance_date'])); ?></td>
            <td><?php echo ucfirst($record['status']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php include 'templates/footer.php'; ?>
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Template Engine** | Jinja2 (separate) | Native PHP tags |
| **Variable Access** | `{{ variable }}` | `<?php echo $variable; ?>` |
| **Conditionals** | `{% if %}...{% endif %}` | `<?php if...?>...<?php endif; ?>` |
| **Loops** | `{% for item in items %}` | `<?php foreach` |
| **Filters** | `\| strftime` | Function calls `date()` |
| **File Structure** | Separate .html files | Mixed .php with HTML |
| **Escaping** | Auto-escaping available | Must use `htmlspecialchars()` |

---

## 2. DEPENDENCIES

### 2.1 Python Dependencies (requirements.txt)

```
Flask==2.3.3              # Web framework
psycopg2-binary==2.9.6    # PostgreSQL adapter
bcrypt==4.0.0             # Password hashing
WTForms==3.0.1            # Form validation
Flask-Session==0.5.0      # Session management
click==8.1.7              # CLI utilities
itsdangerous==2.1.2       # Data signing
Jinja2==3.1.2             # Template engine
```

**Total Dependencies:** 8 packages (3 external frameworks, 5 utilities)

**Dependency Management:**
- **Tool:** pip (Python Package Manager)
- **Configuration:** `requirements.txt`
- **Installation:** `pip install -r requirements.txt`
- **Virtual Environment:** `venv` (recommended)
- **Version Control:** Pinned versions prevent breaking changes

---

### 2.2 PHP Dependencies

```php
// No external dependencies required!

// Built-in extensions (already available in PHP 7.4+):
- PostgreSQL extension (pgsql)  // Database connection
- Session extension           // Session management
- Password extension          // password_hash/verify
- Standard Library            // Array functions, date/time

// Required:
- PHP 7.4+
- PostgreSQL client library
- Apache/Nginx web server
```

**Total Dependencies:** 0 external packages (all built-in)

**Dependency Management:**
- **Tool:** None (PHP native)
- **Configuration:** php.ini
- **Installation:** Configure web server
- **Version Control:** Managed by PHP version
- **No Package Manager:** All functionality included

---

### 2.3 Dependency Comparison

| Aspect | Python | PHP |
|--------|--------|-----|
| **Framework Dependencies** | Flask (external) | None (built-in) |
| **Database Driver** | psycopg2 (external) | pg_* functions (built-in) |
| **Password Hashing** | bcrypt (external) | password_* (built-in) |
| **Form Validation** | WTForms (external) | Manual validation (built-in) |
| **Template Engine** | Jinja2 (external) | Native PHP (built-in) |
| **Total External Packages** | 8 | 0 |
| **Dependency Resolution** | pip + virtual env | None |
| **Complexity** | Moderate | Simple |
| **Production Readiness** | Immediate | Immediate |

---

## 3. PROGRAMMING PARADIGM

### 3.1 Object-Oriented Programming (OOP)

**Python - Class-based OOP:**
```python
class User:
    """User model with attributes and methods"""
    
    def __init__(self, user_id, username, email):
        self.user_id = user_id
        self.username = username
        self.email = email
        self._password_hash = None
    
    def set_password(self, password):
        """Set password with bcrypt hashing"""
        self._password_hash = bcrypt.hashpw(password.encode(), bcrypt.gensalt(10))
    
    def verify_password(self, password):
        """Verify password against stored hash"""
        return bcrypt.checkpw(password.encode(), self._password_hash)
    
    def __str__(self):
        return f"User({self.username}, {self.email})"

# Usage
user = User(1, "admin123", "admin@university.edu")
user.set_password("password")
print(user)  # User(admin123, admin@university.edu)
```

**PHP - Procedural + Singleton Pattern:**
```php
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $this->connection = pg_connect(/* connection string */);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function query($sql, $params = []) {
        return pg_query_params($this->connection, $sql, $params);
    }
}

// Procedural function for authentication
function authenticate_user($username, $password) {
    $db = Database::getInstance();
    $result = $db->query(
        "SELECT user_id, password_hash FROM users WHERE username = $1",
        [$username]
    );
    
    if (pg_num_rows($result) > 0) {
        $user = pg_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            return $user['user_id'];
        }
    }
    return false;
}
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Paradigm** | Multi-paradigm (OOP + Functional) | Procedural + OOP |
| **Primary Style** | Class-based OOP | Procedural functions |
| **Design Patterns** | Inheritance, Mixins, Decorators | Singleton, Factory |
| **Encapsulation** | Private/Protected attributes | Public/Private methods |
| **Code Organization** | Modules + Classes | Functions + Classes |
| **Example** | User class with methods | Standalone functions |

---

### 3.2 Functional Programming Features

**Python - First-Class Functions:**
```python
from functools import wraps

# Decorators (function composition)
def require_login(f):
    """Decorator to require authentication"""
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user_id' not in session:
            return redirect('/auth/login')
        return f(*args, **kwargs)
    return decorated_function

@app.route('/students')
@require_login
def list_students():
    return render_template('students/list.html', students=get_students())

# Higher-order functions
def apply_filter(items, predicate):
    return [item for item in items if predicate(item)]

active_students = apply_filter(students, lambda s: s.is_active)

# Map/Filter/Reduce
enrollment_numbers = list(map(lambda s: s.enrollment_number, students))
present_today = list(filter(lambda a: a.status == 'present', attendance))
```

**PHP - Limited Functional Support:**
```php
// No decorators (PHP doesn't support function composition)
// Authentication via conditional check in each function

function list_students() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '?action=login');
    }
    
    $students = get_students();
    return render('students/list.html', ['students' => $students]);
}

// Anonymous functions (Closures - PHP 5.3+)
$apply_filter = function($items, $predicate) {
    return array_filter($items, $predicate);
};

$active_students = $apply_filter($students, function($s) {
    return $s['is_active'] == true;
});

// Array functions (limited functional style)
$enrollment_numbers = array_map(
    function($s) { return $s['enrollment_number']; },
    $students
);
$present_today = array_filter(
    $attendance,
    function($a) { return $a['status'] == 'present'; }
);
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Decorators** | ✅ Native support | ❌ Not supported |
| **Function Composition** | ✅ Elegant via decorators | ❌ Require manual wrapping |
| **Lambda Functions** | ✅ `lambda x: x.value` | ✅ `function($x) { return $x; }` |
| **Higher-Order Functions** | ✅ Seamless integration | ⚠️ Verbose syntax |
| **Map/Filter/Reduce** | ✅ Built-in (comprehensions) | ✅ array_map/array_filter |
| **Functional Style** | Primary option | Secondary option |

---

### 3.3 Dynamic Typing vs Weak Typing

**Python - Dynamic Typing (Strong):**
```python
# Type is checked at runtime
def calculate_attendance_rate(present_count, total_count):
    """Calculate attendance percentage"""
    if total_count == 0:
        return 0
    
    rate = (present_count / total_count) * 100
    return round(rate, 2)

rate = calculate_attendance_rate(4, 6)  # 66.67
rate = calculate_attendance_rate("4", 6)  # TypeError: unsupported operand

# Type hints for clarity (optional)
def calculate_attendance_rate(present_count: int, total_count: int) -> float:
    if total_count == 0:
        return 0.0
    return round((present_count / total_count) * 100, 2)
```

**PHP - Weak Typing (Automatic Conversion):**
```php
// Automatic type conversion
function calculate_attendance_rate($present_count, $total_count) {
    if ($total_count == 0) {
        return 0;
    }
    
    // String "4" is auto-converted to int 4
    $rate = ($present_count / $total_count) * 100;
    return round($rate, 2);
}

$rate = calculate_attendance_rate(4, 6);      // 66.67
$rate = calculate_attendance_rate("4", 6);    // 66.67 (no error!)
$rate = calculate_attendance_rate("four", 6); // Division by zero warning, 0

// PHP 7.0+ type declarations (optional)
function calculate_attendance_rate(int $present_count, int $total_count): float {
    if ($total_count == 0) {
        return 0.0;
    }
    return round(($present_count / $total_count) * 100, 2);
}

// Type casting is available
$int_value = (int)"42";  // 42
$str_value = (string)42; // "42"
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Type Checking** | Runtime (strict) | Runtime (lenient) |
| **Automatic Conversion** | No (TypeError raised) | Yes (silent conversion) |
| **Type Hints** | Optional (3.5+) | Optional (7.0+) |
| **Implicit Casting** | None | Extensive |
| **Safety** | Higher (catches bugs) | Lower (unexpected behavior) |
| **Flexibility** | Moderate | High |

---

## 4. MEMORY MANAGEMENT

### 4.1 Automatic Garbage Collection

**Python - Reference Counting + Mark-and-Sweep:**
```python
# Reference counting
student = {
    'id': 1,
    'name': 'Alice Brown',
    'enrollments': []  # Nested object
}

# Reference count: 1 (student variable)
temp = student  # Reference count: 2
del temp        # Reference count: 1

# When reference count reaches 0, memory is freed immediately
del student  # Reference count: 0 → Memory freed

# For circular references, Mark-and-Sweep kicks in
class Node:
    def __init__(self, value):
        self.value = value
        self.next = None

a = Node(1)
b = Node(2)
a.next = b
b.next = a  # Circular reference!

del a  # Reference count 1, but Mark-and-Sweep will detect cycle
del b  # Both collected by garbage collector
```

**PHP - Reference Counting with Cycle Collection:**
```php
// Reference counting (similar to Python)
$student = array(
    'id' => 1,
    'name' => 'Alice Brown',
    'enrollments' => array()
);

// Reference count: 1 (variable)
$temp = $student;  // Reference count: 2
unset($temp);      // Reference count: 1

// When reference count reaches 0, memory freed
unset($student);  // Reference count: 0 → Memory freed

// Circular references handled by cycle collector
class Node {
    public $value;
    public $next = null;
}

$a = new Node();
$a->value = 1;
$b = new Node();
$b->value = 2;

$a->next = $b;
$b->next = $a;  // Circular reference

unset($a);  // Cycle detector runs periodically
unset($b);  // Objects freed by garbage collector
```

**Differences:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Collection Method** | Reference counting + Mark-and-Sweep | Reference counting + Cycle Collection |
| **Immediate Cleanup** | Yes (ref count = 0) | Yes (ref count = 0) |
| **Circular Reference Detection** | Generational GC (optional) | Cycle collector (active) |
| **Manual Memory Management** | `del` statement | `unset()` function |
| **Memory Leaks** | Rare (cycles handled) | Rare (cycles handled) |

---

### 4.2 Memory Usage Patterns

**Python - Object Memory:**
```python
import sys

# Check object size
student_data = {
    'user_id': 1,
    'first_name': 'Alice',
    'last_name': 'Brown',
    'email': 'alice@university.edu',
    'phone': '5551234567'
}

print(sys.getsizeof(student_data))  # ~280 bytes (dict overhead)
print(sys.getsizeof(student_data['first_name']))  # ~54 bytes (string)

# List of students
students = [student_data] * 6
print(sys.getsizeof(students))  # ~72 bytes (list overhead)
# Actual memory: 6 references to same dict = efficient!

# String interning
a = "admin"
b = "admin"
print(a is b)  # True (same object in memory)
```

**PHP - Array Memory:**
```php
// Check array size
$student_data = array(
    'user_id' => 1,
    'first_name' => 'Alice',
    'last_name' => 'Brown',
    'email' => 'alice@university.edu',
    'phone' => '5551234567'
);

// No built-in memory size function like Python
// Estimated: ~400-500 bytes per array (more overhead than Python dict)

// Array of students
$students = array_fill(0, 6, $student_data);
// Each reference increases memory (PHP 7+ optimizes copy-on-write)

// String handling
$a = "admin";
$b = "admin";
// Both may reference same interned string (PHP optimizes)
```

---

### 4.3 Performance Implications

**Python:**
```python
def process_attendance_records(records):
    """Process large attendance dataset"""
    results = []
    
    for record in records:  # Memory-efficient iteration
        # Generator expression (lazy evaluation)
        stats = (
            record['status'] 
            for record in records 
            if record['status'] == 'present'
        )
        results.extend(stats)
    
    # Memory freed after function exits
    return results
```

**PHP:**
```php
function process_attendance_records($records) {
    // PHP arrays are value types (copy-on-write in PHP 7+)
    $results = array();
    
    foreach ($records as $record) {  // Efficient iteration
        // Array operations (create new arrays)
        if ($record['status'] == 'present') {
            $results[] = $record['status'];
        }
    }
    
    // Memory freed after function exits
    return $results;
}
```

**Comparison Summary:**
| Aspect | Python | PHP |
|--------|--------|-----|
| **Memory Model** | Object-based | Array/Value-based |
| **Reference Counting** | Automatic | Automatic |
| **Cycle Detection** | Generational GC | Active cycle collector |
| **String Interning** | Yes (common strings) | Partial |
| **Copy-on-Write** | Limited | PHP 7+ supports |
| **Generator Support** | ✅ Native | ❌ Not native |
| **Memory Efficiency** | High | Good |

---

## 5. SUMMARY TABLE: Key Paradigm Differences

| Feature | Python | PHP | Winner |
|---------|--------|-----|--------|
| **Type Safety** | Strong typing | Weak typing | Python |
| **Dependencies** | 8 external packages | 0 (all built-in) | PHP |
| **Syntax Simplicity** | Clean, readable | Verbose | Python |
| **OOP Support** | Native, elegant | Native, basic | Python |
| **Functional Features** | Excellent (decorators) | Basic | Python |
| **Performance** | Good | Excellent (optimized for web) | PHP |
| **Learning Curve** | Gentle | Moderate | Python |
| **Production Ready** | Yes | Yes | Tie |
| **Memory Management** | Automatic + GC | Automatic + GC | Tie |
| **Database Integration** | Via external library | Built-in | PHP |

---

## 6. CONCLUSION

### Python Advantages:
- **Syntax:** Clean, readable, Pythonic
- **Paradigm:** Multi-paradigm (OOP + Functional)
- **Features:** Decorators, generators, comprehensions
- **Type Safety:** Strong typing prevents bugs
- **Learning:** Easier for beginners

### PHP Advantages:
- **Simplicity:** No dependency management needed
- **Integration:** Built-in web server, database, sessions
- **Performance:** Optimized for web applications
- **Cost:** No framework dependencies
- **Rapid Deployment:** Minimal setup required

### Recommendation:
- **Python:** Enterprise applications, complex logic, data processing
- **PHP:** Quick web development, simple CRUD applications, shared hosting

Both paradigms successfully implement the same **Students' Management & Attendance System** with equivalent functionality, demonstrating that paradigm choice depends on project requirements rather than capability.

---

**Document completed:** May 6, 2026  
**Total implementations compared:** 2 languages, 15 core pages, 100% feature parity
