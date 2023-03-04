<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\NexmoMessage;
use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectApproveNotification extends Notification implements ShouldQueue
{
    use Queueable;


    public $user;
    public $project;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($user, $project)
    {
        $this->user = $user;
        $this->project = $project;
        /*$this->school = Company::find(session('current_company'));;*/
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }



    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */


    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Report Approval Notification')
            ->greeting('Hello '.$this->user->full_name.'')
            ->line('Your Report has been approved.')
            ->action('Go to application', url('/'))
            ->line('Thank you');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */



    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
