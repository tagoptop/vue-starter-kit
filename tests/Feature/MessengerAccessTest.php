<?php

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

it('allows customers to view their conversations', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $staff = User::factory()->create(['role' => 'staff']);

    $conversation = Conversation::create([
        'admin_id' => $staff->id,
        'customer_id' => $customer->id,
        'subject' => 'Delivery concern',
        'last_message_at' => now(),
    ]);

    Message::create([
        'conversation_id' => $conversation->id,
        'sender_id' => $staff->id,
        'body' => 'We are checking your request.',
    ]);

    $response = $this->actingAs($customer)->get(route('conversations.index'));

    $response->assertOk();
    $response->assertSee('Messages');
    $response->assertSee('Delivery concern');
    $response->assertSee($staff->name);
});

it('allows customers to send messages in their conversations', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $staff = User::factory()->create(['role' => 'staff']);

    $conversation = Conversation::create([
        'admin_id' => $staff->id,
        'customer_id' => $customer->id,
        'subject' => 'Project follow-up',
    ]);

    $response = $this->actingAs($customer)->post(route('messages.store', $conversation), [
        'body' => 'Please confirm delivery time.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Message sent successfully');

    expect($conversation->messages()->latest('id')->first())
        ->sender_id->toBe($customer->id)
        ->body->toBe('Please confirm delivery time.');
});

it('allows customers to start a conversation with staff', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $staff = User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($customer)->post(route('conversations.store'), [
        'participant_id' => $staff->id,
        'subject' => 'Need help with my order',
    ]);

    $response->assertRedirect();

    $conversation = Conversation::first();

    expect($conversation)
        ->admin_id->toBe($staff->id)
        ->customer_id->toBe($customer->id)
        ->subject->toBe('Need help with my order');
});

it('uses the default support contact for customers without manual selection', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin']);
    User::factory()->create(['role' => 'staff']);

    $response = $this->actingAs($customer)->post(route('conversations.store'), [
        'subject' => 'Need support now',
    ]);

    $response->assertRedirect();

    $conversation = Conversation::first();

    expect($conversation)
        ->admin_id->toBe($admin->id)
        ->customer_id->toBe($customer->id)
        ->subject->toBe('Need support now');
});

it('shows the default support contact on the customer create page', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Primary Support']);

    $response = $this->actingAs($customer)->get(route('conversations.create'));

    $response->assertOk();
    $response->assertSee('Default Support Contact');
    $response->assertSee('Primary Support');
    $response->assertSee('Contact Support');
    $response->assertSee((string) $admin->id, false);
});