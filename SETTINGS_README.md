# Settings Page

## Overview

The Settings page provides users with a comprehensive interface to manage their account settings, privacy preferences, and permissions. The page is fully responsive and works across desktop, tablet, and mobile devices.

## Features

### Basic Settings
- **Username**: Update display name
- **Email**: Change email address with validation
- **Subscription Plan**: View current plan (read-only)

### Privacy Settings
- **Profile Visibility**: Control whether profile is visible to others
- **Tribute Display**: Choose to show tributes to others
- **Tribute Requests**: Allow others to request tributes
- **Email Notifications**: Receive email notifications

### Permissions
- **Legacy Messages**: Allow others to send legacy messages
- **Family Management**: Let family members manage profile after passing
- **AI Suggestions**: Allow AI-generated memorial suggestions

## Technical Implementation

### Frontend Components

#### Settings.vue
Main page component that:
- Loads user data and existing settings
- Displays success/error messages
- Uses modular SettingsForm component

#### SettingsForm.vue
Reusable form component that:
- Handles form submission
- Validates input fields
- Shows validation errors
- Provides Cancel/Save functionality

### Backend Implementation

#### SettingsController
Handles form submission with:
- Input validation
- User data updates
- Settings storage in JSON format
- Success/error message handling

#### Database Schema
- Added `settings` JSON column to users table
- Stores all settings as structured JSON data
- Provides default values for new users

#### User Model
- Added settings attribute with defaults
- Automatic JSON casting
- Fallback to default settings

## Responsive Design

The settings page is fully responsive with:

### Desktop (1024px+)
- Full-width form layout
- Side-by-side buttons
- Optimal spacing and typography

### Tablet (768px - 1023px)
- Adjusted spacing
- Maintained readability
- Touch-friendly checkboxes

### Mobile (320px - 767px)
- Stacked button layout
- Optimized touch targets
- Simplified spacing

## Form Validation

### Client-side
- Real-time validation feedback
- Visual error indicators
- Disabled submit during processing

### Server-side
- Required field validation
- Email format validation
- Unique email constraint
- Boolean validation for checkboxes

## Usage

1. Navigate to `/settings` (requires authentication)
2. Modify desired settings
3. Click "Save" to update
4. Click "Cancel" to reset changes

## File Structure

```
resources/js/
├── pages/
│   └── Settings.vue          # Main settings page
├── components/
│   └── SettingsForm.vue      # Reusable form component
└── types/
    └── index.d.ts            # TypeScript definitions

app/
├── Http/Controllers/
│   └── SettingsController.php # Backend controller
└── Models/
    └── User.php              # User model with settings

database/migrations/
└── 2025_08_06_073210_add_settings_to_users_table.php
```

## Future Enhancements

- Add more subscription plan options
- Implement advanced privacy controls
- Add export/import settings functionality
- Create settings backup/restore feature
- Add audit trail for settings changes 