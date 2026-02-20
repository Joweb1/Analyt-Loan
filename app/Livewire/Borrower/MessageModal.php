<?php

namespace App\Livewire\Borrower;

use App\Helpers\SystemLogger;
use App\Models\Borrower;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MessageModal extends Component
{
    public Borrower $borrower;

    public $showModal = false;

    public $title;

    public $message;

    public $priority = 'medium';

    protected $listeners = ['openMessageModal' => 'open'];

    public function mount(Borrower $borrower)
    {
        $this->borrower = $borrower;
    }

    public function open($borrowerId)
    {
        if (! Auth::user()->hasPermissionTo('send_customer_messages')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to send direct messages to customers.']);

            return;
        }

        if ($this->borrower->id === $borrowerId) {
            $this->showModal = true;
        }
    }

    public function sendMessage()
    {
        if (! Auth::user()->hasPermissionTo('send_customer_messages')) {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'You do not have permission to perform this action.']);

            return;
        }

        $this->validate([
            'title' => 'required|string|max:100',
            'message' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high,critical',
        ]);

        if ($this->borrower->user_id) {
            SystemLogger::log(
                $this->title,
                $this->message,
                'info',
                'message',
                $this->borrower,
                false,
                null,
                $this->priority,
                $this->borrower->user_id
            );

            $this->dispatch('custom-alert', ['type' => 'success', 'message' => 'Message sent to '.$this->borrower->user->name]);
            $this->reset(['title', 'message', 'priority', 'showModal']);
        } else {
            $this->dispatch('custom-alert', ['type' => 'error', 'message' => 'This borrower does not have a user account to receive notifications.']);
        }
    }

    public function render()
    {
        return view('livewire.borrower.message-modal');
    }
}
