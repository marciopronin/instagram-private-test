# Feedback

**WARNING:** YOU SHOULD NOT USE THE API FOR SPAMMING PURPOSES. IF YOU DO, YOU ARE DOING IT UNDER YOUR OWN RESPONSABILITY. MAKE A RESPONSIBLE USAGE OF THE API AND DO NOT MAKE SPAM.


The Feedback Required (`feedback_required` ) error is usually caused by the following:

- Targeted account error
- Performing activities too fast
- Targeted Hashtags are Banned
- Posting Spammy comments

The above reasons are just a few among the possible cause of this issue, however, they are the most common reasons.

1. **Targeted account error:** You can encounter the Instagram Feedback required error when there is an issue with the account you’re trying to interact with. This could be as a result of privacy settings or the user have decided to block your account.

In this case, you can simply ignore the feedback required error as you won’t encounter issues when you moved on to the next targeted account. In case you think there is a mistake by Instagram, you should call the following function:

```json
{"message": "feedback_required", "spam": true, "feedback_title": "Action Blocked", "feedback_message": "This action was blocked. Please try again later. We restrict certain content and actions to protect our community. Tell us if you think we made a mistake.", "feedback_url": "repute/report_problem/ instagram_follow_users/", "feedback_appeal_label": "Tell us", "feedback_ignore_label": "OK", "feedback_action": "report_problem", "status": "fail"}
```

```php
$ig->internal->reportProblem($e->getResponse()->getFeedbackUrl());
```

Sometimes your account will be just limited because your account made too many actions not because Instagram thinks you are doing spam, in that case you will receive this other response:

```json
{"message": "feedback_required", "comment_error_key": "comment_generic", "feedback_ignore_label": "OK", "feedback_title": "Couldn't Post Your Comment", "feedback_message": "Comments on this post have been limited", "feedback_required": true, "status": "fail"}
```

In the above message there is no `feedback_url` because there is nothing to report, your account is temporarily limited and you will have to wait until you can make any action again. The time this restriction goes away are usually from some hours to 2 days.

2. **Performing Activities too fast:** If you perform tasks too fast or you have hit the Instagram limits of your account, you are bound to face this error also. There is a set limit for performing various tasks on Instagram such as Follow, Unfollow, Likes and Comments.
For new accounts, the limit is low compared to old and trusted accounts. You should set logic limits to avoid `InstagramAPI\Response\GenericResponse: Feedback required` when they’re hitting the limit to avoid getting the account banned.

3. Targeted Hashtags are Blacklisted: Sometimes, the issue might not be with your account or your activity, it could be your target. Instagram constantly ban hashtags that have been abused, if you include such hashtag in your targeted tags, then you might encounter this error.

4. Posting Spammy comments: If you post comments that Instagram suspects to be a spam, you might be stopped from performing this task and the action required error might come up.
For new Instagram accounts, errors like this do occur if you have not verified your account, especially with a mobile number and if you’ve not used the account on the official Instagram mobile app. In this case, we advise you to verify the account (preferably with a mobile number) and also perform tasks such as Follow, Like and comment on the official Instagram mobile app.