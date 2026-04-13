<?php

use App\Models\User;

test('guests cannot access assistant compose endpoint', function () {
    $this->get(route('assistant.compose', ['command_id' => 'draft-quote-project-type']))
        ->assertRedirect(route('login'));
});

test('authenticated users can compose assistant prompts', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('assistant.compose', [
        'command_id' => 'draft-quote-project-type',
        'placeholders' => [
            'Project_Type' => 'residential slab',
        ],
        'goal' => 'Generate a quote quickly',
        'context' => 'Main counter request',
        'source' => 'Inventory Master List',
    ]));

    $response->assertOk()
        ->assertJsonPath('command.id', 'draft-quote-project-type')
        ->assertJsonPath('prompt', "/Draft Quote for residential slab\nGoal: Generate a quote quickly\nContext: Main counter request\nSource: Inventory Master List");
});

test('compose endpoint returns validation error for unknown command', function () {
    $this->actingAs(User::factory()->create());

    $response = $this->get(route('assistant.compose', [
        'command_id' => 'not-a-real-command',
    ]));

    $response->assertUnprocessable()
        ->assertJson([
            'message' => 'Unknown assistant command.',
        ]);
});
