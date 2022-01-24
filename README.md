# EE Objects Members

This library allows developers to treat ExpressionEngine Members as objects within their Addons. 

### The Problems This Solve

There are two main points this library covers; canonical keys and data types. With the first party Member Model within ExpressionEngine, you're dealing with mostly raw data delivered within a raw format. Specifically, custom fields are delivered in their raw database key and the raw value. 

This can complicate development so this library removes that concern. 

## Requirements
- ExpressionEngine >= 5.5
- PHP >= 7.1
 
## Installation

Add `ee-objects/members` as a requirement to your `composer.json`:

```bash
$ composer require ee-objects/members
```

### Implementation

```php
use EeObjects\Members\Member;

$member = ee('your-addon-name:MembersService')->getMember($member_id);
if ($member instanceof Member) {

    $first_name = $member->get('first_name');

    $member->set('first_name', 'Eric');
    $member->save();

    $member->delete();
}
```

## Docs

Available in the [Wiki](https://github.com/EE-Objects/Members/wiki "Wiki") and the [EeObjects Addon](https://github.com/EE-Objects/Example-Addon) repository