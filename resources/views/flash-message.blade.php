@if ($message = Session::get('success'))
<div class="rounded-md border border-green-200 bg-green-50 text-green-800 px-4 py-3 mb-3 text-center relative" x-data="{ open: true }" x-show="open">
    <button type="button" class="absolute right-3 top-2 text-green-800/70 hover:text-green-800" @click="open = false">&times;</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('error'))
<div class="rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-3 mb-3 text-center relative" x-data="{ open: true }" x-show="open">
    <button type="button" class="absolute right-3 top-2 text-red-800/70 hover:text-red-800" @click="open = false">&times;</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('warning'))
<div class="rounded-md border border-yellow-200 bg-yellow-50 text-yellow-800 px-4 py-3 mb-3 text-center relative" x-data="{ open: true }" x-show="open">
    <button type="button" class="absolute right-3 top-2 text-yellow-800/70 hover:text-yellow-800" @click="open = false">&times;</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($message = Session::get('info'))
<div class="rounded-md border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3 mb-3 text-center relative" x-data="{ open: true }" x-show="open">
    <button type="button" class="absolute right-3 top-2 text-blue-800/70 hover:text-blue-800" @click="open = false">&times;</button>
    <span class="text-xl inline-block mr-1 align-middle">
        <i class="fa fa-bell"></i>
    </span>
    <strong>{{ $message }}</strong>
</div>
@endif

@if ($errors->any())
<div class="rounded-md border border-red-200 bg-red-50 text-red-800 px-4 py-3 mb-3 text-center relative" x-data="{ open: true }" x-show="open">
    <button type="button" class="absolute right-3 top-2 text-red-800/70 hover:text-red-800" @click="open = false">&times;</button>
    Please check the form below for errors
    <div class="mt-2 text-left">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif