# Genit IO Chat Laravel

A Laravel package for managing user contacts with a one-to-one relationship. Each user gets a unique contact with a chat ID (UUID) for integration with external chat services.

## Features

- **One contact per user**: Each user has exactly one contact record with a unique chat UUID
- **Simple contact management**: Store and retrieve user contact information
- **Easy to use service**: Simple API for managing contacts
- **Automatic migrations**: Database tables are created automatically

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x

## Installation

### 1. Install via Composer

Add the package to your `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "../gio-chat-laravel"
    }
  ]
}
```

Then install:

```bash
composer require genit-io/chat-laravel
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=chat-config
```

### 3. Run Migrations

The package migrations will run automatically, or you can publish them:

```bash
php artisan vendor:publish --tag=chat-migrations
php artisan migrate
```

## Configuration

The config file (`config/chat.php`) allows you to customize:

```php
return [
    // User model to use for relationships
    'user_model' => env('CHAT_USER_MODEL', 'App\Models\User'),

    // Table names
    'tables' => [
        'genit_io_chats' => 'genit_io_chats',
    ],

    // Pagination settings
    'pagination' => [
        'default_limit' => 100,
        'max_limit' => 500,
    ],
];
```

## Database Schema

### `genit_io_contacts` Table

- `id`: Primary key
- `user_id`: Foreign key to users table (unique - one contact per user)
- `contact_id`: UUID (unique) - identifier for external chat integration
- `timestamps`

## Usage

### Using the Facade

Add the facade to your class:

```php
use GenitIo\Chat\Facades\Contact;
```

### Using Dependency Injection

```php
use GenitIo\Chat\Services\ChatService;

class MyController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }
}
```

### Basic Operations

#### Get User's Contact

```php
use GenitIo\Chat\Facades\Contact;

// Get contact for user
$contact = Contact::getContact(userId: 1);

// Get user's chat ID (UUID)
$chatId = Contact::getContactId(userId: 1);
```

#### Create Contact

```php
// Create a contact for the user
$contact = Contact::createContact(userId: 1);

// Create contact with specific chat ID
$contact = Contact::createContact(userId: 1, chatId: 'your-uuid-here');
```

#### Delete Contact

```php
// Delete user's contact
Contact::deleteContact(userId: 1);
```

### Using Models Directly

```php
use GenitIo\Chat\Models\Contact;

// Get user's contact
$contact = Contact::where('user_id', 1)->first();

// Access chat_id (UUID)
echo $contact->chat_id;

// Create contact
$contact = Contact::create([
    'user_id' => 1,
    'chat_id' => 'your-uuid-here', // optional
]);
```

## API Controller Example

```php
use GenitIo\Chat\Services\ChatService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(protected ChatService $chatService)
    {
    }

    public function getContact(Request $request)
    {
        $contact = $this->chatService->getContact($request->user()->id);

        return response()->json([
            'contact_id' => $contact?->id,
            'chat_id' => $contact?->chat_id,
        ]);
    }

    public function createContact(Request $request)
    {
        $contact = $this->chatService->createContact(
            userId: $request->user()->id,
            chatId: $request->input('chat_id')
        );

        return response()->json($contact);
    }

    public function deleteContact(Request $request)
    {
        $deleted = $this->chatService->deleteContact($request->user()->id);

        return response()->json(['success' => $deleted]);
    }
}
```

## Testing

```bash
composer test
```

## License

MIT License

## Support

For issues and questions, please open an issue on GitHub.

## User Model Integration

You can add the `HasContact` trait to your User model for convenient access:

```php
use GenitIo\Chat\Traits\HasContact;

class User extends Authenticatable
{
    use HasContact;

    // ... rest of your model
}
```

Then you can use:

```php
$user = User::find(1);

// Create contact
$contact = $user->createContact();

// Get chat ID
$chatId = $user->getChatId();

// Check if user has contact
if ($user->hasContact()) {
    // ...
}

// Access contact relationship
$contact = $user->contact;
```
