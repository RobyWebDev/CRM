<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Project;
use App\Models\Retainer;
use App\Models\ServiceType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aktivitás-idővonal (crm_projekt.md 8. szekció) — a spatie/laravel-activitylog
 * csomag korábban telepítve volt, de egyetlen modellen sem volt bekötve, így az
 * activity_log tábla mindig üres maradt. Ez a teszt azt ellenőrzi, hogy a
 * HasActivityTimeline trait bekötése után ténylegesen naplóz.
 */
class ActivityTimelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_and_updating_a_contact_is_logged_with_the_acting_user_as_causer(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Napló Teszt']);
        $contact->update(['phone' => '06301234567']);

        $activities = $contact->activities()->get();

        $this->assertCount(2, $activities);
        $this->assertSame('created', $activities->first()->description);
        $this->assertSame('updated', $activities->last()->description);
        $this->assertSame($user->id, $activities->last()->causer_id);
        $this->assertArrayHasKey('phone', $activities->last()->changes()->get('attributes'));
    }

    public function test_deal_activity_is_logged_and_the_edit_page_renders_the_timeline(): void
    {
        $user = User::factory()->create();
        $serviceType = ServiceType::create(['account_id' => $user->account_id, 'name' => 'Coaching', 'slug' => 'coaching']);
        $pipeline = Pipeline::create(['account_id' => $user->account_id, 'service_type_id' => $serviceType->id, 'name' => 'Alap', 'is_default' => true]);
        $stage = PipelineStage::create(['pipeline_id' => $pipeline->id, 'name' => 'Érdeklődés', 'sort_order' => 1]);

        $this->actingAs($user);
        $deal = Deal::create([
            'account_id' => $user->account_id,
            'pipeline_id' => $pipeline->id,
            'pipeline_stage_id' => $stage->id,
            'title' => 'Napló-teszt üzlet',
            'status' => 'open',
        ]);

        $this->assertCount(1, $deal->activities);

        $response = $this->get("/deals/{$deal->id}/edit");
        $response->assertOk()->assertSee(__('Aktivitás'));
    }

    public function test_contact_project_and_retainer_pages_render_the_activity_timeline(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contact = Contact::create(['account_id' => $user->account_id, 'first_name' => 'Render Teszt']);
        $project = Project::create(['account_id' => $user->account_id, 'title' => 'Render-teszt projekt']);
        $retainer = Retainer::create(['account_id' => $user->account_id, 'title' => 'Render-teszt retainer']);

        $this->get("/contacts/{$contact->id}")->assertOk()->assertSee(__('Aktivitás'));
        $this->get("/projects/{$project->id}")->assertOk()->assertSee(__('Aktivitás'));
        $this->get("/retainers/{$retainer->id}")->assertOk()->assertSee(__('Aktivitás'));
    }
}
