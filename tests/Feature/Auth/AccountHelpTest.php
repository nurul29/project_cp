<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountRequestStatus;
use App\Models\AccountRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AccountHelpTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_an_account_help_dialog(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login'));
    }

    public function test_guest_can_submit_a_password_recovery_request_without_account_enumeration(): void
    {
        $user = User::factory()->create([
            'username' => 'siswa.081',
            'email' => 'siswa@example.test',
        ]);

        $response = $this->from(route('login'))
            ->post(route('account-help.store'), ['username' => $user->username]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success', 'Permintaan bantuan dicatat. Admin sekolah akan memverifikasi identitas melalui prosedur sekolah.');
        $this->assertDatabaseHas('account_requests', [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'status' => AccountRequestStatus::Pending->value,
        ]);

        $unknownResponse = $this->from(route('login'))
            ->post(route('account-help.store'), ['username' => 'unknown.user']);

        $unknownResponse->assertRedirect(route('login'));
        $unknownResponse->assertSessionHas('success', 'Permintaan bantuan dicatat. Admin sekolah akan memverifikasi identitas melalui prosedur sekolah.');
        $this->assertDatabaseHas('account_requests', [
            'name' => 'unknown.user',
            'username' => 'unknown.user',
            'email' => null,
            'status' => AccountRequestStatus::Pending->value,
        ]);
        $this->assertSame(2, AccountRequest::query()->count());
    }

    public function test_account_help_requires_a_username(): void
    {
        $this->from(route('login'))
            ->post(route('account-help.store'), ['username' => ''])
            ->assertSessionHasErrors('username');

        $this->assertSame(0, AccountRequest::query()->count());
    }
}
