<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            User Avatar
        </h2>

    </header>

    @if (session('message'))
        <div class="text-red-500">
            {{ session('message') }}
        </div>
    @endif
    

    <img 
        src='{{ "/storage/$user->avatar" }}' 
        alt="avatar image"
        width="50"
        height="50"
        class="rounded-full"
        onerror="this.onerror=null;this.src='img/no-avatar-available.jpg';"
    >

    <form 
      action="{{ route('profile.avatar.ai') }}" 
      method="POST"
      class="mt-4"
    >
        @csrf

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Generate Avatar from AI
        </p>

        <x-primary-button>Generate Avatar</x-primary-button>
    </form>


    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400" style="margin-bottom: -10px;">
        Or:
    </p>

    <form 
      method="post" 
      action="{{ route('profile.avatar') }}"
      class="mt-6 space-y-6"
      enctype="multipart/form-data"
    >
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Upload an avatar image from your HD" />
            <x-text-input id="avatar" name="avatar" type="file" class="mt-1 block w-full" :value="old('avatar', $user->avatar)" required autofocus autocomplete="avatar" />
            <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
