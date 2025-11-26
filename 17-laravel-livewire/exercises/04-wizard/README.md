# 04 - Multi-Step Form Wizard

## Objective
Build a multi-step form wizard using Livewire to handle complex form flows with validation at each step.

## Prerequisites
- Completed "01-component", "02-todo", and "03-search" exercises
- Understanding of Livewire state management
- Knowledge of form validation

## Instructions

### Step 1: Create Models
```bash
php artisan make:model Registration -m
php artisan make:model RegistrationStep -m
```

Edit `registrations` migration:

```php
Schema::create('registrations', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('status')->default('pending'); // pending, completed, cancelled
    $table->json('data')->nullable();
    $table->timestamps();
});
```

Edit `registration_steps` migration:

```php
Schema::create('registration_steps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('registration_id')->constrained()->onDelete('cascade');
    $table->integer('step_number');
    $table->string('step_name');
    $table->boolean('completed')->default(false);
    $table->timestamps();
});
```

Run migrations:

```bash
php artisan migrate
```

### Step 2: Define Models
In `app/Models/Registration.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Registration extends Model
{
    protected $fillable = ['email', 'status', 'data'];

    protected $casts = ['data' => 'array'];

    public function steps(): HasMany
    {
        return $this->hasMany(RegistrationStep::class);
    }

    public function getData(): array
    {
        return $this->data ?? [];
    }

    public function updateData(array $newData): void
    {
        $current = $this->getData();
        $this->update(['data' => array_merge($current, $newData)]);
    }
}
```

In `app/Models/RegistrationStep.php`:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrationStep extends Model
{
    protected $fillable = ['registration_id', 'step_number', 'step_name', 'completed'];
}
```

### Step 3: Create Wizard Component
```bash
php artisan make:livewire RegistrationWizard
```

In `app/Livewire/RegistrationWizard.php`:

```php
namespace App\Livewire;

use App\Models\Registration;
use Livewire\Component;
use Livewire\Attributes\Validate;

class RegistrationWizard extends Component
{
    public int $currentStep = 1;
    public int $totalSteps = 4;
    public ?Registration $registration = null;

    // Step 1: Personal Info
    #[Validate('required|string|min:3', as: 'first name')]
    public string $firstName = '';

    #[Validate('required|string|min:3', as: 'last name')]
    public string $lastName = '';

    #[Validate('required|email|unique:registrations,email', as: 'email')]
    public string $email = '';

    // Step 2: Company Info
    #[Validate('required|string|min:3', as: 'company name')]
    public string $companyName = '';

    #[Validate('required|string|in:startup,small,medium,enterprise', as: 'company size')]
    public string $companySize = '';

    #[Validate('required|string|min:10', as: 'company description')]
    public string $companyDescription = '';

    // Step 3: Plan Selection
    #[Validate('required|string|in:starter,professional,enterprise', as: 'plan')]
    public string $selectedPlan = '';

    // Step 4: Confirmation
    #[Validate('accepted', as: 'terms')]
    public bool $agreeTerms = false;

    public function mount(): void
    {
        // Initialize from session if editing
        if (session()->has('registration_id')) {
            $this->registration = Registration::find(session('registration_id'));
            if ($this->registration) {
                $data = $this->registration->getData();
                $this->firstName = $data['firstName'] ?? '';
                $this->lastName = $data['lastName'] ?? '';
                $this->email = $data['email'] ?? '';
                $this->companyName = $data['companyName'] ?? '';
                $this->companySize = $data['companySize'] ?? '';
                $this->companyDescription = $data['companyDescription'] ?? '';
                $this->selectedPlan = $data['selectedPlan'] ?? '';
            }
        }
    }

    public function nextStep(): void
    {
        match($this->currentStep) {
            1 => $this->validateStep1(),
            2 => $this->validateStep2(),
            3 => $this->validateStep3(),
            default => null,
        };

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
            $this->saveProgress();
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function validateStep1(): void
    {
        $this->validateOnly(['firstName', 'lastName', 'email']);
    }

    public function validateStep2(): void
    {
        $this->validateOnly(['companyName', 'companySize', 'companyDescription']);
    }

    public function validateStep3(): void
    {
        $this->validateOnly('selectedPlan');
    }

    public function saveProgress(): void
    {
        if (!$this->registration) {
            $this->registration = Registration::create([
                'email' => $this->email,
                'status' => 'pending',
            ]);
        }

        $this->registration->updateData([
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'companyName' => $this->companyName,
            'companySize' => $this->companySize,
            'companyDescription' => $this->companyDescription,
            'selectedPlan' => $this->selectedPlan,
        ]);

        session(['registration_id' => $this->registration->id]);
    }

    public function submit(): void
    {
        $this->validateOnly('agreeTerms');

        // Final validation of all steps
        $this->validate();

        // Process the registration
        if ($this->registration) {
            $this->registration->update([
                'status' => 'completed',
            ]);
        }

        session()->forget('registration_id');
        session()->flash('success', 'Registration completed successfully!');
        $this->redirect('/dashboard');
    }

    public function cancel(): void
    {
        if ($this->registration) {
            $this->registration->update(['status' => 'cancelled']);
        }
        session()->forget('registration_id');
        $this->redirect('/');
    }

    public function getProgressProperty(): float
    {
        return ($this->currentStep / $this->totalSteps) * 100;
    }

    public function render()
    {
        return view('livewire.registration-wizard', [
            'progress' => $this->progress,
        ]);
    }
}
```

### Step 4: Create Wizard View
In `resources/views/livewire/registration-wizard.blade.php`:

```blade
<div class="w-full max-w-2xl mx-auto p-6 bg-white rounded shadow-lg">
    <h1 class="text-3xl font-bold mb-2">Account Registration</h1>
    <p class="text-gray-600 mb-6">Step {{ $currentStep }} of {{ $totalSteps }}</p>

    {{-- Progress Bar --}}
    <div class="w-full bg-gray-200 rounded-full h-2 mb-8">
        <div class="bg-blue-500 h-2 rounded-full transition-all"
             style="width: {{ $progress }}%"></div>
    </div>

    <form wire:submit="@if($currentStep === $totalSteps) submit @else nextStep @endif">
        {{-- Step 1: Personal Information --}}
        @if($currentStep === 1)
            <div class="space-y-4">
                <h2 class="text-xl font-bold mb-4">Personal Information</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="firstName" class="block font-bold mb-2">First Name *</label>
                        <input type="text"
                               id="firstName"
                               wire:model="firstName"
                               placeholder="John"
                               class="w-full px-4 py-2 border rounded @error('firstName') border-red-500 @enderror">
                        @error('firstName')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="lastName" class="block font-bold mb-2">Last Name *</label>
                        <input type="text"
                               id="lastName"
                               wire:model="lastName"
                               placeholder="Doe"
                               class="w-full px-4 py-2 border rounded @error('lastName') border-red-500 @enderror">
                        @error('lastName')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="block font-bold mb-2">Email Address *</label>
                    <input type="email"
                           id="email"
                           wire:model="email"
                           placeholder="john@example.com"
                           class="w-full px-4 py-2 border rounded @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        {{-- Step 2: Company Information --}}
        @if($currentStep === 2)
            <div class="space-y-4">
                <h2 class="text-xl font-bold mb-4">Company Information</h2>

                <div>
                    <label for="companyName" class="block font-bold mb-2">Company Name *</label>
                    <input type="text"
                           id="companyName"
                           wire:model="companyName"
                           placeholder="Acme Inc."
                           class="w-full px-4 py-2 border rounded @error('companyName') border-red-500 @enderror">
                    @error('companyName')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="companySize" class="block font-bold mb-2">Company Size *</label>
                    <select id="companySize"
                            wire:model="companySize"
                            class="w-full px-4 py-2 border rounded @error('companySize') border-red-500 @enderror">
                        <option value="">Select size...</option>
                        <option value="startup">Startup (1-10)</option>
                        <option value="small">Small (11-50)</option>
                        <option value="medium">Medium (51-250)</option>
                        <option value="enterprise">Enterprise (250+)</option>
                    </select>
                    @error('companySize')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="companyDescription" class="block font-bold mb-2">Company Description *</label>
                    <textarea id="companyDescription"
                              wire:model="companyDescription"
                              placeholder="Tell us about your company..."
                              rows="4"
                              class="w-full px-4 py-2 border rounded @error('companyDescription') border-red-500 @enderror"></textarea>
                    @error('companyDescription')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        {{-- Step 3: Plan Selection --}}
        @if($currentStep === 3)
            <div class="space-y-4">
                <h2 class="text-xl font-bold mb-4">Select Your Plan</h2>

                <div class="grid grid-cols-3 gap-4">
                    @foreach(['starter' => ['name' => 'Starter', 'price' => '$9/mo', 'features' => ['5 projects', '1 user', 'Basic support']], 'professional' => ['name' => 'Professional', 'price' => '$29/mo', 'features' => ['Unlimited projects', '10 users', 'Priority support']], 'enterprise' => ['name' => 'Enterprise', 'price' => 'Custom', 'features' => ['Everything', 'Unlimited users', '24/7 support']]] as $plan => $details)
                        <div wire:click="$set('selectedPlan', '{{ $plan }}')"
                             @class(['p-4 border-2 rounded cursor-pointer transition', 'border-blue-500 bg-blue-50' => $selectedPlan === $plan, 'border-gray-200 hover:border-gray-300' => $selectedPlan !== $plan])>
                            <h3 class="font-bold text-lg">{{ $details['name'] }}</h3>
                            <p class="text-2xl font-bold text-green-600 my-2">{{ $details['price'] }}</p>
                            <ul class="text-sm space-y-1">
                                @foreach($details['features'] as $feature)
                                    <li class="flex items-center">
                                        <span class="text-green-500 mr-2">✓</span>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>

                @error('selectedPlan')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Step 4: Confirmation --}}
        @if($currentStep === 4)
            <div class="space-y-4">
                <h2 class="text-xl font-bold mb-4">Confirm Your Information</h2>

                <div class="p-4 bg-gray-50 rounded space-y-3">
                    <div>
                        <p class="text-gray-600">Name</p>
                        <p class="font-bold">{{ $firstName }} {{ $lastName }}</p>
                    </div>

                    <div>
                        <p class="text-gray-600">Email</p>
                        <p class="font-bold">{{ $email }}</p>
                    </div>

                    <div>
                        <p class="text-gray-600">Company</p>
                        <p class="font-bold">{{ $companyName }} ({{ ucfirst($companySize) }})</p>
                    </div>

                    <div>
                        <p class="text-gray-600">Plan</p>
                        <p class="font-bold">{{ ucfirst($selectedPlan) }}</p>
                    </div>
                </div>

                <div>
                    <label class="flex items-start gap-2">
                        <input type="checkbox"
                               wire:model="agreeTerms"
                               class="mt-1">
                        <span>I agree to the terms and conditions *</span>
                    </label>
                    @error('agreeTerms')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        {{-- Navigation Buttons --}}
        <div class="flex gap-4 mt-8">
            @if($currentStep > 1)
                <button type="button"
                        wire:click="previousStep"
                        class="px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Previous
                </button>
            @endif

            @if($currentStep < $totalSteps)
                <button type="submit"
                        class="flex-1 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Next
                </button>
            @else
                <button type="submit"
                        class="flex-1 px-6 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Complete Registration
                </button>
            @endif

            <button type="button"
                    wire:click="cancel"
                    class="px-6 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                Cancel
            </button>
        </div>
    </form>
</div>
```

### Step 5: Create Route
In `routes/web.php`:

```php
use App\Livewire\RegistrationWizard;

Route::get('/register', RegistrationWizard::class)->name('register');
```

### Step 6: Add Link in Navigation
Update navigation to include registration link.

### Step 7: Test Wizard
1. Go to `/register`
2. Fill in step 1, click next
3. Fill in step 2, click next
4. Select a plan in step 3, click next
5. Review and accept terms
6. Complete registration
7. Verify data saved in database

## Deliverables
- [ ] Registration and RegistrationStep models created
- [ ] Livewire wizard component working
- [ ] All 4 steps functional
- [ ] Step-by-step validation working
- [ ] Progress bar updating
- [ ] Previous button navigating correctly
- [ ] Data persisted between steps
- [ ] Final submission working
- [ ] Plan selection displayed correctly
- [ ] Confirmation step showing correct data
- [ ] Session management for draft saves

## Resources
- [Livewire Components](https://livewire.laravel.com/docs/components)
- [Form Handling](https://livewire.laravel.com/docs/forms)
- [Validation](https://livewire.laravel.com/docs/validation)
- [Lifecycle](https://livewire.laravel.com/docs/lifecycle)

## Tips
- Use validateOnly() for step-specific validation
- Save progress to database for draft recovery
- Show progress bar for user experience
- Disable next button if validation fails
- Use confirm() for cancellation
- Consider email verification after registration
- Add captcha for security
- Test with slow networks
