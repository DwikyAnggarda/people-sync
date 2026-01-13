1. Migration **master data dulu**
2. Pivot & relasi
3. Core business tables
4. Index & constraint
5. Baru Model + Relationship

# 📁 STRUKTUR FILE (TARGET)

```
database/migrations/
app/Models/
```

---

# 1️⃣ USERS

> Laravel default → **PAKAI**, hanya kita tambahkan `deleted_at`

### Migration

```php
Schema::table('users', function (Blueprint $table) {
    $table->softDeletes();
});
```

### Partial Unique Index (PostgreSQL)

```php
DB::statement("
    CREATE UNIQUE INDEX users_email_unique
    ON users(email)
    WHERE deleted_at IS NULL
");
```

### Model

```php
class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password'];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }
}
```

---

# 2️⃣ ROLES

### Migration

```php
Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->timestamps();
});
```

### Model

```php
class Role extends Model
{
    protected $fillable = ['name'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
```

---

# 3️⃣ USER_ROLES (Pivot)

### Migration

```php
Schema::create('user_roles', function (Blueprint $table) {
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('role_id')->constrained()->cascadeOnDelete();

    $table->primary(['user_id', 'role_id']);
});
```

---

# 4️⃣ DEPARTMENTS

### Migration

```php
Schema::create('departments', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()
          ->constrained('departments')
          ->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

### Model

```php
class Department extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'parent_id'];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
```

---

# 5️⃣ EMPLOYEES ⭐

### Migration

```php
Schema::create('employees', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()
          ->constrained()
          ->nullOnDelete();

    $table->string('employee_number');
    $table->string('name');
    $table->string('email')->nullable();
    $table->foreignId('department_id')->constrained();
    $table->string('status')->default('active');
    $table->date('joined_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});
```

### Partial Unique Index

```php
DB::statement("
    CREATE UNIQUE INDEX employees_number_unique
    ON employees(employee_number)
    WHERE deleted_at IS NULL
");
```

### Model

```php
class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'name',
        'email',
        'department_id',
        'status',
        'joined_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }
}
```

---

# 6️⃣ ATTENDANCES (CORE)

### Migration

```php
Schema::create('attendances', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained();
    $table->date('date');
    $table->timestamp('clock_in_at');
    $table->timestamp('clock_out_at')->nullable();
    $table->string('photo_path')->nullable();
    $table->decimal('latitude', 10, 7)->nullable();
    $table->decimal('longitude', 10, 7)->nullable();
    $table->string('source')->default('mobile');
    $table->timestamps();
});
```

### Index

```php
$table->index(['employee_id', 'date']);
```

### Model

```php
class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_at',
        'clock_out_at',
        'photo_path',
        'latitude',
        'longitude',
        'source'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
```

---

# 7️⃣ LEAVES

### Migration

```php
Schema::create('leaves', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained();
    $table->string('type');
    $table->date('start_date');
    $table->date('end_date');
    $table->string('status')->default('pending');
    $table->text('reason')->nullable();
    $table->foreignId('approved_by')->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

---

# 8️⃣ OVERTIMES

### Migration

```php
Schema::create('overtimes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained();
    $table->date('date');
    $table->time('start_time');
    $table->time('end_time');
    $table->string('status')->default('pending');
    $table->text('reason')->nullable();
    $table->foreignId('approved_by')->nullable()
          ->constrained('users')
          ->nullOnDelete();
    $table->timestamps();
    $table->softDeletes();
});
```

---