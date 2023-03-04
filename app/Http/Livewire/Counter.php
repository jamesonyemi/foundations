<?php

namespace App\Http\Livewire;

use App\User;
use Livewire\Component;

class Counter extends Component
{
    public $count;

    public function increment()
    {
        /*$this->count++;*/
        $this->count = User::get()->last()->first_name;
    }

    public function decrement()
    {
        /*$this->count--;*/
        $this->count = User::get()->first()->first_name;
    }

    public function render()
    {
        return view('livewire.counter', [
            $this->count = User::get()->last()->first_name,
        ]);
    }
}
