<?php

namespace Tests\Feature;

use App\Models\SavedFilter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Mentett szűrők/nézetek (crm_projekt.md 8. szekció) — a legkritikusabb
 * elvárás itt is az elkülönítés: sem másik account, sem ugyanannak az
 * accountnak egy másik felhasználója nem láthatja/törölheti a szűrőt.
 */
class SavedFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_saved_filter_can_be_created_for_the_current_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/saved-filters', [
            'resource' => 'leads',
            'name' => 'Forró leadjeim',
            'query_string' => 'status=qualified',
        ]);

        $response->assertRedirect();

        $filter = SavedFilter::first();
        $this->assertSame($user->account_id, $filter->account_id);
        $this->assertSame($user->id, $filter->user_id);
        $this->assertSame('status=qualified', $filter->query_string);
    }

    public function test_a_user_cannot_delete_another_users_saved_filter_within_the_same_account(): void
    {
        $owner = User::factory()->create();
        $colleague = User::factory()->create(['account_id' => $owner->account_id]);

        $filter = SavedFilter::create([
            'account_id' => $owner->account_id,
            'user_id' => $owner->id,
            'resource' => 'leads',
            'name' => 'Owner szűrője',
            'query_string' => 'status=new',
        ]);

        $this->actingAs($colleague)->delete("/saved-filters/{$filter->id}")->assertForbidden();
        $this->assertNotNull($filter->fresh());
    }

    public function test_a_user_cannot_delete_another_accounts_saved_filter(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $filter = SavedFilter::create([
            'account_id' => $owner->account_id,
            'user_id' => $owner->id,
            'resource' => 'contacts',
            'name' => 'Privát szűrő',
            'query_string' => 'tag=vip',
        ]);

        $this->actingAs($intruder)->delete("/saved-filters/{$filter->id}")->assertNotFound();
    }

    public function test_invalid_resource_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/saved-filters', [
            'resource' => 'deals',
            'name' => 'Nem támogatott erőforrás',
        ]);

        $response->assertSessionHasErrors('resource');
    }

    public function test_contacts_and_leads_index_render_with_saved_filters_component(): void
    {
        $user = User::factory()->create();

        SavedFilter::create([
            'account_id' => $user->account_id,
            'user_id' => $user->id,
            'resource' => 'leads',
            'name' => 'Forró leadjeim',
            'query_string' => 'status=qualified',
        ]);

        $this->actingAs($user)->get('/contacts?q=teszt')->assertOk()->assertSee('Jelenlegi szűrés mentése');
        $this->actingAs($user)->get('/leads?status=qualified')->assertOk()->assertSee('Forró leadjeim');
    }
}
