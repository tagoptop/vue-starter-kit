<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantComposeController extends Controller
{
    /**
     * Compose an assistant prompt from a known command definition.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'command_id' => ['required', 'string'],
            'placeholders' => ['array'],
            'goal' => ['nullable', 'string'],
            'context' => ['nullable', 'string'],
            'source' => ['nullable', 'string'],
        ]);

        $command = config('assistant.commands.' . $validated['command_id']);

        if (! $command) {
            return response()->json([
                'message' => 'Unknown assistant command.',
            ], 422);
        }

        $placeholders = $validated['placeholders'] ?? [];
        $prompt = $this->buildPrompt($command, $placeholders, $validated);

        return response()->json([
            'command' => [
                'id' => $command['id'],
                'title' => $command['title'],
            ],
            'prompt' => $prompt,
        ]);
    }

    /**
     * Build the prompt text for a known command.
     */
    private function buildPrompt(array $command, array $placeholders, array $validated): string
    {
        $parts = [];
        $label = $command['title'];

        if (! empty($command['prompt_placeholder'])) {
            $placeholderKey = $command['prompt_placeholder'];
            $placeholderValue = $placeholders[$placeholderKey] ?? '';
            $parts[] = '/' . $label . ' for ' . $placeholderValue;
        } else {
            $parts[] = '/' . $label;
        }

        if (! empty($validated['goal'])) {
            $parts[] = 'Goal: ' . $validated['goal'];
        }

        if (! empty($validated['context'])) {
            $parts[] = 'Context: ' . $validated['context'];
        }

        if (! empty($validated['source'])) {
            $parts[] = 'Source: ' . $validated['source'];
        }

        return implode("\n", $parts);
    }
}