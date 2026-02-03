<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ForumContentApproved;
use App\Models\ActivityLog;
use App\Models\ForumBadge;
use App\Models\ForumEngagement;
use App\Models\ForumPost;
use App\Models\ForumTopic;
use App\Models\UserBadge;
use App\Models\WeeklyChallenge;
use App\Services\ActivityLogger;
use App\Services\BadgeService;
use App\Support\EmailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AlumniForumModerationController extends Controller
{
    public function __construct(private ActivityLogger $logger)
    {
    }

    public function index()
    {
        $pendingTopics = ForumTopic::where('is_approved', false)->latest('created_at')->get();
        $pendingPosts = ForumPost::where('is_approved', false)->with(['user', 'topic'])->latest('created_at')->get();
        $engagementTrends = ForumEngagement::recentWeek()->get();
        $engagementSummary = [
            'topics_created' => $engagementTrends->sum('topics_created'),
            'posts_created' => $engagementTrends->sum('posts_created'),
            'topics_approved' => $engagementTrends->sum('topics_approved'),
            'posts_approved' => $engagementTrends->sum('posts_approved'),
        ];
        $recentActivities = ActivityLog::with('user')
            ->where('action', 'like', 'forum.%')
            ->latest('created_at')
            ->limit(6)
            ->get();

        $topBadges = ForumBadge::withCount('users')
            ->orderByDesc('users_count')
            ->take(3)
            ->get();

        $challenge = WeeklyChallenge::active();

        $stats = [
            'pending_topics' => $pendingTopics->count(),
            'pending_posts' => $pendingPosts->count(),
            'topics_approved' => $engagementSummary['topics_approved'],
            'posts_approved' => $engagementSummary['posts_approved'],
        ];

        return view('admin.alumni_forum.moderation', compact(
            'pendingTopics',
            'pendingPosts',
            'engagementTrends',
            'engagementSummary',
            'recentActivities',
            'topBadges',
            'challenge',
            'stats'
        ));
    }

    public function approveTopic(Request $request, ForumTopic $forumTopic)
    {
        $forumTopic->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ForumEngagement::incrementField('topics_approved');
        $forumTopic->load('user');
        if ($forumTopic->user && $forumTopic->user->email && EmailSettings::notificationsEnabled()) {
            Mail::to($forumTopic->user->email)->send(new ForumContentApproved(
                'Topik',
                $forumTopic->title,
                route('alumni.forum.show', $forumTopic)
            ));
        }

        BadgeService::evaluate($forumTopic->user);

        $this->logger->log(
            user: $request->user(),
            action: 'forum.topic.approve',
            description: "Topik '{$forumTopic->title}' disetujui",
            subject: $forumTopic
        );

        return back()->with('success', 'Topik berhasil disetujui.');
    }

    public function rejectTopic(Request $request, ForumTopic $forumTopic)
    {
        $this->logger->log(
            user: $request->user(),
            action: 'forum.topic.reject',
            description: "Topik '{$forumTopic->title}' ditolak dan dihapus",
            subject: $forumTopic
        );

        $forumTopic->delete();

        return back()->with('success', 'Topik ditolak dan dihapus.');
    }

    public function approvePost(Request $request, ForumPost $forumPost)
    {
        $forumPost->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ForumEngagement::incrementField('posts_approved');
        $forumPost->loadMissing('topic');
        if ($forumPost->topic && $forumPost->user && $forumPost->user->email && EmailSettings::notificationsEnabled()) {
            $topicUrl = route('alumni.forum.show', ['topic' => $forumPost->topic->slug]);
            Mail::to($forumPost->user->email)->send(new ForumContentApproved(
                'Balasan',
                Str::limit($forumPost->content, 40),
                $topicUrl
            ));
        }

        BadgeService::evaluate($forumPost->user);

        $this->logger->log(
            user: $request->user(),
            action: 'forum.post.approve',
            description: "Balasan oleh {$forumPost->user->name} disetujui",
            subject: $forumPost
        );

        return back()->with('success', 'Balasan berhasil disetujui.');
    }

    public function rejectPost(Request $request, ForumPost $forumPost)
    {
        $topicTitle = $forumPost->topic->title;

        $this->logger->log(
            user: $request->user(),
            action: 'forum.post.reject',
            description: "Balasan dalam topik '{$topicTitle}' ditolak dan dihapus",
            subject: $forumPost
        );

        $forumPost->delete();

        return back()->with('success', 'Balasan ditolak dan dihapus.');
    }
}
