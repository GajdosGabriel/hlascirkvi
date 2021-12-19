<?php

namespace App\Http\Livewire\Admin\Table;

use App\Models\User;
use Livewire\Component;


class UserTable extends Component
{
    public $search;

    public function render()
    {
        return view('livewire.admin.table.userTable')->with(['users' => $this->users()] );
    }

    public function users() {
        return User::withTrashed()
        ->where('first_name', 'like', '%'. $this->search .'%' )
        ->orWhere('last_name', 'like', '%'. $this->search .'%' )
        ->orWhere('email', 'like', '%'. $this->search .'%' )
            ->latest()->paginate(80);
    }
}
