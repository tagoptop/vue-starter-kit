<?php

namespace Tests\Feature;

use App\Models\OutgoingProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutgoingProductWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_outgoing_products_page()
    {
        $this->get(route('outgoing-products.index'))
            ->assertRedirect(route('login'));
    }

    public function test_staff_can_create_outgoing_product_record()
    {
        $staff = User::factory()->staff()->create();

        $this->actingAs($staff)
            ->post(route('outgoing-products.store'), [
                'product_name' => 'Network Switch',
                'quantity' => 5,
                'destination' => 'Branch Warehouse',
            ])
            ->assertRedirect(route('outgoing-products.index'));

        $this->assertDatabaseHas('outgoing_products', [
            'product_name' => 'Network Switch',
            'quantity' => 5,
            'destination' => 'Branch Warehouse',
            'status' => 'draft',
            'prepared_by' => $staff->id,
            'checked_by' => null,
        ]);
    }

    public function test_non_checker_cannot_release_outgoing_product()
    {
        $staff = User::factory()->staff()->create();
        $product = OutgoingProduct::create([
            'product_name' => 'Laptop',
            'quantity' => 2,
            'destination' => 'HQ',
            'status' => 'draft',
            'prepared_by' => $staff->id,
        ]);

        $this->actingAs($staff)
            ->patch(route('outgoing-products.release', $product))
            ->assertForbidden();

        $this->assertDatabaseHas('outgoing_products', [
            'id' => $product->id,
            'status' => 'draft',
            'checked_by' => null,
        ]);
    }

    public function test_checker_can_release_draft_product()
    {
        $staff = User::factory()->staff()->create();
        $checker = User::factory()->checker()->create();

        $product = OutgoingProduct::create([
            'product_name' => 'Power Supply',
            'quantity' => 10,
            'destination' => 'Client Site',
            'status' => 'draft',
            'prepared_by' => $staff->id,
        ]);

        $this->actingAs($checker)
            ->patch(route('outgoing-products.release', $product))
            ->assertRedirect(route('outgoing-products.index'));

        $this->assertDatabaseHas('outgoing_products', [
            'id' => $product->id,
            'status' => 'released',
            'checked_by' => $checker->id,
        ]);

        $this->assertNotNull($product->refresh()->released_at);
    }

    public function test_checker_can_mark_released_product_as_delivered()
    {
        $staff = User::factory()->staff()->create();
        $checker = User::factory()->checker()->create();

        $product = OutgoingProduct::create([
            'product_name' => 'UPS',
            'quantity' => 1,
            'destination' => 'Data Center',
            'status' => 'released',
            'prepared_by' => $staff->id,
            'checked_by' => $checker->id,
            'released_at' => now(),
        ]);

        $this->actingAs($checker)
            ->patch(route('outgoing-products.deliver', $product))
            ->assertRedirect(route('outgoing-products.index'));

        $this->assertDatabaseHas('outgoing_products', [
            'id' => $product->id,
            'status' => 'delivered',
            'checked_by' => $checker->id,
        ]);

        $this->assertNotNull($product->refresh()->delivered_at);
    }
}
