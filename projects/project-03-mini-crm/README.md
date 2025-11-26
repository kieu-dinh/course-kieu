# Project 03 - Mini CRM

## Objective

Build a professional CRM (Customer Relationship Management) application using the full TALL stack.

## Duration
2-3 weeks

## Requirements

### Features
- [ ] User authentication with roles (admin, user)
- [ ] Companies CRUD
- [ ] Contacts CRUD (belong to companies)
- [ ] Notes for contacts
- [ ] Activity log (who did what, when)
- [ ] Dashboard with stats
- [ ] Search and filters
- [ ] Export to CSV

### Technical
- [ ] Laravel with Livewire
- [ ] Clean architecture (Services, Repositories)
- [ ] Proper authorization
- [ ] Database relationships
- [ ] Tailwind + Alpine.js
- [ ] Testing

---

## Database Schema

```
users
├── id
├── name
├── email
├── role (admin, user)
└── timestamps

companies
├── id
├── name
├── email
├── phone
├── website
├── address
└── timestamps

contacts
├── id
├── company_id (FK)
├── first_name
├── last_name
├── email
├── phone
├── position
└── timestamps

notes
├── id
├── contact_id (FK)
├── user_id (FK)
├── content
└── timestamps

activities
├── id
├── user_id (FK)
├── type (created, updated, deleted)
├── subject_type (Company, Contact)
├── subject_id
├── description
└── timestamps
```

---

## Pages

| Route | Description |
|-------|-------------|
| `/dashboard` | Stats overview |
| `/companies` | List companies |
| `/companies/{id}` | Company details + contacts |
| `/contacts` | List all contacts |
| `/contacts/{id}` | Contact details + notes |
| `/activities` | Activity log |
| `/users` | User management (admin) |

---

## Architecture

```
app/
├── Http/Controllers/
├── Livewire/
│   ├── Companies/
│   │   ├── CompanyList.php
│   │   ├── CompanyForm.php
│   │   └── CompanyShow.php
│   └── Contacts/
│       ├── ContactList.php
│       └── ...
├── Models/
├── Services/
│   ├── CompanyService.php
│   └── ContactService.php
└── Repositories/
    ├── CompanyRepository.php
    └── ContactRepository.php
```

---

## Steps

### Week 1
1. Create project, install packages
2. Design database, create migrations
3. Create models with relationships
4. Implement auth with roles
5. Build Companies CRUD with Livewire

### Week 2
6. Build Contacts CRUD with Livewire
7. Add notes functionality
8. Add activity logging
9. Build dashboard with stats
10. Add search and filters

### Week 3
11. Add CSV export
12. Implement Services and Repositories
13. Write tests
14. Polish UI
15. Final testing and deployment

---

## Livewire Components

```bash
php artisan make:livewire Companies/CompanyList
php artisan make:livewire Companies/CompanyForm
php artisan make:livewire Companies/CompanyShow
php artisan make:livewire Contacts/ContactList
# ... etc
```

---

## Bonus

- Email integration (send emails to contacts)
- Import from CSV
- Tags for contacts
- Reminders/Tasks
- File attachments

---

## Checklist

- [ ] Auth with roles works
- [ ] Companies CRUD works
- [ ] Contacts CRUD works
- [ ] Notes work
- [ ] Activities are logged
- [ ] Dashboard shows stats
- [ ] Search works
- [ ] Export works
- [ ] Code is well-organized
- [ ] Tests pass
- [ ] Responsive design
- [ ] Pushed to GitHub

---

## Congratulations!

Completing this project means you're ready for real-world work! 🎉

You now have:
- A solid understanding of web development
- Experience with the TALL stack
- A portfolio of projects
- The skills to learn more on your own

Keep building and learning!
