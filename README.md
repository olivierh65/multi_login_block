# Multi Login Block

A flexible Drupal module that provides a customizable login block displaying multiple authentication methods in collapsible detail elements.

## Overview

The Multi Login Block module creates a block that allows users to access multiple login methods from a single location:

- **Standard Drupal Login**: The default Drupal user login form
- **Social Authentication**: Support for multiple OAuth providers (Google, Facebook, GitHub, LinkedIn, Twitter, Microsoft, etc.)

Each login method is presented in an HTML `<details>` element, allowing users to expand only the method they need.

## Features

### Core Features

- ✅ Configurable standard Drupal login form integration
- ✅ Dynamic social authentication provider detection
- ✅ Support for any `social_auth_*` module installed on the site
- ✅ Per-provider configuration (enable/disable, labels, custom URLs)
- ✅ Collapsible interface with details elements
- ✅ Option to set default open state for each login method
- ✅ Custom button text and labels
- ✅ User context caching for performance

### Supported Social Auth Providers

The module automatically detects and supports the following social authentication modules when installed:

- **social_auth_google** - Google OAuth
- **social_auth_facebook** - Facebook Login
- **social_auth_github** - GitHub OAuth
- **social_auth_linkedin** - LinkedIn OAuth
- **social_auth_twitter** - Twitter/X OAuth
- **social_auth_microsoft** - Microsoft/Azure OAuth

Additional social auth providers are supported as long as they follow the standard `social_auth_*` naming convention and implement the standard `social_auth.network.redirect` route.

## Installation

1. Place the module in your custom modules directory:
   ```bash
   web/modules/custom/multi_login_block/
   ```

2. Enable the module through the UI or using Drush:
   ```bash
   drush en multi_login_block
   ```

3. Clear cache:
   ```bash
   drush cr
   ```

## Configuration

### Block Placement

1. Navigate to **Structure > Block Layout** in the Drupal admin
2. Select your desired theme
3. Click **Place block**
4. Search for and select "Multi Login Block"
5. Configure placement and save

### Block Settings

Once the block is placed, you can configure it by editing the block:

#### Standard Login Configuration

- **Enable standard login** - Toggle the Drupal login form visibility
- **Label** - Customize the label displayed for the standard login method (default: "Connexion standard")
- **Open by default** - When checked, the standard login details element will be expanded on page load

#### Social Auth Providers Configuration

For each detected social auth provider:

- **Enable [Provider]** - Toggle this provider's visibility in the block
- **Label** - The main label displayed for this provider (default: provider name)
- **Network identifier** - The network key used in the route (e.g., "google", "facebook")
- **Custom URL** - Optional: Provide a custom OAuth URL instead of using the default `social_auth.network.redirect` route
- **Button text** - Text displayed on the authentication button (default: "Login")
- **Open by default** - When checked, this provider's details element will be expanded on page load

## Requirements

- Drupal 9, 10, or 11
- Core `user` module (enabled by default)
- Optional: Any `social_auth_*` module for OAuth provider support

## File Structure

```
multi_login_block/
├── README.md                                    # This file
├── multi_login_block.info.yml                   # Module definition
├── multi_login_block.module                     # Theme hook definition
├── multi_login_block.libraries.yml              # CSS/JS libraries
├── css/
│   └── multi-login-block.css                    # Block styling
├── src/
│   └── Plugin/Block/
│       └── MultiLoginBlock.php                  # Main block plugin
└── templates/
    └── multi-login-block.html.twig              # Block template
```

## Development & Customization

### Theming

The block uses the `multi_login_block` theme, which is rendered by the `multi-login-block.html.twig` template.

#### Template Variables

The template receives:
- `login_methods` - Array of login methods, each containing:
  - `id` - Unique identifier (e.g., "standard", "google", "facebook")
  - `label` - Display label
  - `icon` - Icon identifier used for CSS classes
  - `content` - Renderable content (form or markup)
  - `open_default` - Boolean indicating if details should open by default

#### CSS Classes

The block and its elements use the following CSS classes:
- `.multi-login-block` - Main wrapper
- `.multi-login-method` - Details element for each method
- `.multi-login-method--{id}` - Method-specific class (e.g., `.multi-login-method--google`)
- `.multi-login-summary` - Summary element (clickable header)
- `.multi-login-icon` - Icon container
- `.multi-login-icon--{icon}` - Icon-specific class (e.g., `.multi-login-icon--google`)
- `.multi-login-label` - Label text
- `.multi-login-content` - Content wrapper

Social auth buttons also receive:
- `.button` - Standard button class
- `.button--primary` - Primary button styling
- `.oauth-button` - OAuth-specific class
- `.oauth-{provider}` - Provider-specific class (e.g., `.oauth-google`)

### Extending the Module

#### Adding Custom Providers

To add support for custom authentication providers beyond social auth:

1. Implement a custom block plugin extending `MultiLoginBlock`
2. Override the `getSocialAuthProviders()` method to include custom providers
3. Modify the `build()` method to handle custom provider logic

#### Creating a Submodule

You can create a submodule that extends Multi Login Block functionality:

```php
// In your custom module
function my_module_form_block_form_multi_login_block_alter(&$form, FormStateInterface $form_state) {
  // Add custom fields to block configuration
}
```

## Hooks

The module implements the following hook:

- `hook_theme()` - Defines the `multi_login_block` theme

## Caching

The rendered block is cached using the `user` context, meaning the block output is cached per user but regenerated when the user context changes (e.g., after login/logout).

## Troubleshooting

### Social Auth Providers Not Appearing

If social auth providers don't appear in the block configuration:

1. Ensure the corresponding `social_auth_*` module is installed and enabled
2. Clear the Drupal cache: `drush cr`
3. Edit the block configuration again to see the newly available providers

### Custom URLs Not Working

When using custom OAuth URLs:

- Ensure the URL is correctly formatted and accessible
- Test the URL in your browser to verify it works
- Check browser console for any redirect or security errors (CORS, mixed content, etc.)

### Login Form Not Displaying

- Verify the "Enable standard login" option is checked in block settings
- Ensure the `user` module is enabled
- Check that user registration/login is permitted in admin settings

## Module Information

- **Name**: Multi Login Block
- **Type**: Custom Module
- **Package**: Custom
- **Core Compatibility**: Drupal 9, 10, 11
- **Dependencies**: drupal:user

## Author Notes

This module provides a clean, user-friendly interface for sites supporting multiple authentication methods. The collapsible details element approach keeps the interface compact while remaining accessible and intuitive.

The automatic detection of social auth modules means new providers can be added simply by installing the corresponding module—no code changes required.

## License

Same as Drupal
