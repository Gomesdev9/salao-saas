<x-page :title="__('Profile')">
    <div class="flex max-w-3xl flex-col gap-5">
        <div class="cartao p-5 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="cartao p-5 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="cartao p-5 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-page>
