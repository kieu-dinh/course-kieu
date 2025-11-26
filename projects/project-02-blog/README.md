# Project 02 - Blog

## Objective

Build a complete blog with Laravel, including authentication and CRUD.

## Duration
1-2 weeks

## Requirements

### Features
- [ ] User registration and login
- [ ] Create, edit, delete posts (only author)
- [ ] View all posts (public)
- [ ] View single post (public)
- [ ] Categories
- [ ] Search posts
- [ ] Pagination

### Technical
- [ ] Laravel with Breeze
- [ ] Eloquent models and relationships
- [ ] Form validation
- [ ] Authorization (users can only edit own posts)
- [ ] Blade layouts and components
- [ ] Tailwind styling
- [ ] Flash messages

---

## Database Schema

```
users
├── id
├── name
├── email
├── password
└── timestamps

categories
├── id
├── name
├── slug
└── timestamps

posts
├── id
├── user_id (FK)
├── category_id (FK)
├── title
├── slug
├── content (text)
├── published (boolean)
├── published_at (datetime, nullable)
└── timestamps
```

---

## Pages

| Route | Description |
|-------|-------------|
| `/` | Home - latest posts |
| `/posts` | All posts (paginated) |
| `/posts/{slug}` | Single post |
| `/posts/create` | Create post (auth) |
| `/posts/{slug}/edit` | Edit post (auth + owner) |
| `/category/{slug}` | Posts by category |
| `/search?q=term` | Search results |
| `/dashboard` | User's posts |

---

## Steps

1. Create Laravel project with Breeze
2. Design database (migrations)
3. Create models with relationships
4. Create PostController (resource)
5. Build views (layout, index, show, create, edit)
6. Add validation
7. Add authorization (Policy)
8. Add categories
9. Add search
10. Add pagination
11. Style with Tailwind
12. Test thoroughly
13. Push to GitHub

---

## Commands to Remember

```bash
# Create project
laravel new blog

# Install Breeze
composer require laravel/breeze --dev
php artisan breeze:install blade

# Create model with migration
php artisan make:model Post -m
php artisan make:model Category -m

# Create controller
php artisan make:controller PostController --resource

# Create policy
php artisan make:policy PostPolicy --model=Post

# Run migrations
php artisan migrate
```

---

## Bonus

- Add comments
- Add image upload for posts
- Add Markdown support
- Use Livewire for comments

---

## Checklist

- [ ] Auth works (register, login, logout)
- [ ] Can create posts
- [ ] Can edit own posts
- [ ] Cannot edit others' posts
- [ ] Can delete own posts
- [ ] Categories work
- [ ] Search works
- [ ] Pagination works
- [ ] Responsive design
- [ ] Pushed to GitHub
