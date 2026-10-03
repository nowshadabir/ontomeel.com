**Reading Tracker, Credit & Rewards System**

Feature Scope & Functional Requirements

**Project Type:** New module for the existing PHP & MySQL-based website  
**Integration:** Existing Student Membership / Subscription System  
**Document Purpose:** Finalize and approve the functional scope before development begins.

**1\. MODULE OVERVIEW**

A new **Reading Tracker, Credit & Rewards System** will be integrated into the existing website.

The purpose of this module is to allow eligible subscribed students to:

* Add books they are currently reading.

* Provide basic information about each book.

* Record their daily reading activity.

* Record which pages they read.

* Record the amount of time spent reading.

* Optionally save a quote/note from the day's reading.

* Earn credits based on eligible reading time.

* Track accumulated credits.

* View available rewards.

* Set/track a reward goal.

* Request redemption of a reward after meeting its credit requirement.

Administrators will be able to manage the relevant settings, reading limits, credit rules, rewards and redemption requests.

**2\. ELIGIBILITY & SUBSCRIPTION ACCESS**

The Reading Rewards module will work with the website's existing subscription/membership system.

**2.1 Eligible Users**

Only users with an eligible/active subscription will have access to the Reading Rewards features.

The exact subscription plans that qualify for this feature must be determined before implementation.

**2.2 Subscription Status**

The system will check the user's existing subscription status before allowing access to protected Reading Rewards features.

Possible states may include:

* Active

* Expired

* Cancelled

* Inactive

Only the agreed eligible status(es) will allow credit-earning activity.

**2.3 Subscription Expiration**

When a user's subscription expires:

* New reading activities may be restricted.

* New credits may no longer be earned.

* Existing reading history will remain stored.

* Existing accumulated credits will remain stored unless a separate credit-expiration policy is agreed upon.

**To be confirmed:** Whether users with expired subscriptions can still view their reading history, credits and rewards.

**3\. MY BOOKS / READING LIST**

Students will have a section where they can maintain the books they are currently reading.

**3.1 Add a Book**

A student can add a new book to their reading list.

Information may include:

**Required fields:**

* Book Title

* Total Number of Pages

**Optional fields:**

* Author Name

* Book Cover

* Starting Page

* Short description/note

Final fields must be approved before development.

**3.2 Book Ownership**

A book entry will belong to the student who created it.

One student's personal book records will not automatically appear in another student's account.

**3.3 Book Status**

A book can have statuses such as:

* Currently Reading

* Completed

* Paused

The exact statuses will be finalized before implementation.

**3.4 Book Progress**

The system will calculate reading progress based on the student's submitted page information.

Example:

Total pages: **300**

Latest completed page: **120**

Progress:

**120 / 300 pages**

**40% completed**

A visual progress indicator may be displayed.

**3.5 Completing a Book**

When the student reaches the final page, the book can be marked as completed.

The completed book will remain available in the student's reading history.

Reading goal 

**4\. DAILY READING ACTIVITY**

Students will be able to submit reading activity for their books.

A reading activity represents a reading session/day entry.

**4.1 Reading Activity Fields**

The student will select the relevant book and submit:

* Reading Date

* Starting Page

* Ending Page

* Reading Duration

* Optional Quote / Reading Note

Example:

**Book:** Atomic Habits  
**Date:** 10 October  
**From Page:** 51  
**To Page:** 78  
**Reading Time:** 42 minutes  
**Quote/Note:** Optional text

**4.2 Pages Read**

The system will calculate pages read based on the submitted starting and ending pages.

The system will prevent page numbers greater than the book's configured total pages.

**4.3 Reading Duration**

Reading duration will be recorded in minutes.

Example:

**45 minutes**

Whether students enter this manually or use a built-in reading timer must be determined before development.

Unless specifically added to the approved scope, the initial version will use **manual duration submission**.

**5\. OPTIONAL READING QUOTE / NOTE**

While submitting a reading activity, the student may optionally save a quote, thought, or note related to what they read.

Example:

“Small changes often appear to make no difference until you cross a critical threshold.”

This field is intended as a personal reading journal feature.

**5.1 Visibility**

By default, submitted quotes/notes will be associated with the student's reading record.

**6\. CREDIT EARNING SYSTEM**

Students will earn credits for eligible reading time.

Credits will be calculated automatically according to configured rules.

**6.1 Initial Proposed Credit Rule**

The proposed rule is:

**First 100 eligible minutes:**  
0.17 credits per minute

**After the first 100 eligible minutes:**  
0.15 credits per minute

The meaning of "first 100 minutes" must be finalized.

* First 100 minutes of the user's lifetime reading activity

**6.2 Example Calculation**

If the rule applies within the relevant calculation period and the student records 150 eligible minutes:

First 100 minutes:

100 × 0.17 \= **17 credits**

Remaining 50 minutes:

50 × 0.15 \= **7.5 credits**

Total:

**24.5 credits**

**6.3 Credit Precision**

Credits may contain decimals.

Example:

**124.75 Credits**

The rounding/decimal policy will be determined before implementation.

**6.4 Credit Calculation**

Students will not manually enter earned credits.

The system will calculate credits automatically based on eligible reading minutes.

**7\. READING TIME LIMIT**

A maximum amount of reading time eligible for credits will be configured.

This protects the rewards system from unreasonable or excessive submissions.

**7.1 Maximum Eligible Reading Time**

Administrator will be able to configure the maximum eligible reading time.

Example:

Maximum eligible time:

**120 minutes/day**

If a student has already recorded 100 eligible minutes and submits another 40-minute session:

Total submitted: **140 minutes**

Eligible: **120 minutes**

Not eligible for credits: **20 minutes**

The exact behaviour must be finalized.

**7.2 Reading Beyond the Limit**

Two possible approaches are available:

**Option A — Allow but don't reward**

Students can record additional reading activity, but time beyond the limit generates no credits.

**Option B — Block submission**

The system prevents students from submitting reading time exceeding the configured limit.

The selected behaviour must be approved before development.

**7.3 Configurable Limit**

The maximum eligible reading time should be configurable through the admin panel rather than hard-coded.

**8\. READING ENTRY VALIDATION**

The system will perform basic validation before accepting a reading record.

Validation may include:

* Starting page cannot be negative.

* Ending page cannot exceed the book's total pages.

* Ending page cannot be lower than starting page.

* Reading duration must be greater than zero.

* Reading duration cannot exceed applicable system limits.

* User must have access to the selected book.

* User must meet applicable subscription requirements.

**8.1 Duplicate/Overlapping Pages**

The expected behaviour for rereading previously submitted pages must be finalized.

For example:

Yesterday:

Pages **1–30**

Today student submits:

Pages **20–50**

Possible rules:

* Allow rereading and give credits.

* Allow rereading but don't give credits for duplicate pages.

* Prevent overlapping page ranges.

This decision should be approved before development.

**9\. READING HISTORY**

Students will have access to their previous reading activities.

The history may display:

* Date

* Book

* Starting Page

* Ending Page

* Pages Read

* Reading Duration

* Credits Earned

* Quote/Note, if provided

**9.1 Individual Book History**

Students may also view reading history for a specific book.

Example:

**Atomic Habits**

Total pages: 320  
Current page: 185  
Total recorded reading time: 410 minutes  
Reading progress: 57.8%

Followed by previous reading sessions.

**10\. STUDENT CREDIT BALANCE**

Students will have a visible credit balance.

Example:

**Available Credits: 1,420**

Credits will be generated through eligible reading activity according to the approved credit rules.

**10.1 Credit Information**

The student dashboard may show:

* Available Credits

* Total Credits Earned

* Credits Used/Redeemed

If required, the system can maintain a credit transaction history.

Example:

Reading Activity — **\+8.50**

Reading Activity — **\+12.75**

T-Shirt Redemption — **−2,000**

**10.2 Credit Ledger**

For proper tracking, each credit addition or deduction should be recorded as an individual transaction rather than only updating a single balance number.

This allows administrators to identify how a student's balance was calculated.

**11\. REWARDS / GIFTS**

Administrators will be able to create rewards that students can unlock using accumulated credits.

Example rewards:

* T-Shirt

* Book

* Mug

* Merchandise

* Other physical/digital rewards

**11.1 Reward Information**

Each reward may contain:

* Reward Name

* Image

* Short Description

* Required Credits

* Availability Status

Example:

**Premium T-Shirt**

Required:

**2,000 Credits**

**11.2 Reward Availability**

Administrators can activate or deactivate a reward.

An inactive reward will not be available for new redemption requests.

**12\. REWARD GOAL & PROGRESS**

Students will be able to see how close they are to obtaining a reward.

Example:

**T-Shirt**

Current Credits:

**1,800**

Required:

**2,000**

Remaining:

**200 Credits**

Progress:

**90%**

A progress bar will visually represent progress.

**12.1 Goal Selection**

If multiple rewards exist, whether the student explicitly chooses one reward as their current goal or whether progress is automatically shown against every reward must be finalized.

**13\. REWARD REDEMPTION**

When a student has enough available credits, they may request a reward.

Example:

Available balance: **2,150**

T-Shirt requirement: **2,000**

Student selects:

**Redeem T-Shirt**

**13.1 Redemption Request**

A redemption request will be created.

Information may include:

* Student

* Reward

* Credits Required

* Request Date

* Status

**13.2 Redemption Status**

Possible statuses:

* Pending

* Approved

* Delivered/Completed

* Rejected/Cancelled

Final statuses will be approved before implementation.

**13.3 Credit Deduction Timing**

The client must approve when credits are deducted.

Possible approaches:

**Option A:** Deduct immediately when the redemption request is submitted.

**Option B:** Deduct only when the administrator approves the request.

This must be finalized before development.

**13.4 Delivery Information**

If physical rewards are delivered to students, required delivery information must be determined.

This may include:

* Recipient Name

* Phone

* Address

* T-Shirt Size, where applicable

Courier integration or automated delivery booking is **not included** unless specifically added to the approved scope.

**14\. STUDENT DASHBOARD**

A dedicated section will provide students with an overview of their reading activity and rewards.

The dashboard may include:

**Reading Summary**

* Currently Reading

* Books Completed

* Pages Read

* Reading Time

**Credit Summary**

* Available Credits

* Total Earned

* Total Redeemed

**Current Reward Goal**

* Reward

* Required Credits

* Current Credits

* Remaining Credits

* Progress Bar

**Recent Activity**

* Recent reading sessions

* Credits earned

The final dashboard layout will be determined during UI implementation.

**15\. ADMIN — READING MANAGEMENT**

Administrators will have access to student reading records.

Admin may view:

* Student

* Book

* Reading Date

* Page Range

* Reading Duration

* Credits Earned

* Quote/Note

Appropriate filtering/search may be provided based on the existing admin architecture.

**16\. ADMIN — CREDIT SETTINGS**

The administrator will be provided controls for agreed configurable credit settings.

These may include:

**Credit Rate — Tier 1**

Example:

**0.17 credit/minute**

**Tier Threshold**

Example:

**100 minutes**

**Credit Rate — Tier 2**

Example:

**0.15 credit/minute**

**Maximum Eligible Reading Time**

Example:

**120 minutes/day**

Changing a setting will apply to future calculations unless otherwise agreed.

Previously earned credits will **not automatically be recalculated** when rates are changed.

**17\. ADMIN — CREDIT MANAGEMENT**

Administrators may view a student's credit balance and transaction history.

If manual adjustment is included, authorized administrators may:

* Add Credits

* Deduct Credits

A reason should be recorded for manual adjustments.

Example:

**\+100 Credits**  
Reason: Reading Challenge Bonus

Manual credit adjustment functionality must be explicitly approved before implementation.

**18\. ADMIN — REWARD MANAGEMENT**

Administrators will be able to:

* Add Reward

* Edit Reward

* Activate/Deactivate Reward

* Change Required Credits

* Upload/Change Reward Image

* View Redemption Requests

* Update Redemption Status

Deleting rewards that already have transaction/redemption history should be restricted or handled safely to preserve historical records.

**19\. ADMIN — REDEMPTION MANAGEMENT**

Administrators will have a list of reward redemption requests.

Information may include:

* Student

* Reward

* Required Credits

* Request Date

* Current Status

* Delivery Information, if applicable

Administrator can update the request according to the approved redemption workflow.

**20\. DATA & HISTORY**

The system should maintain historical records for:

* Student Books

* Reading Activities

* Credits Earned

* Credit Adjustments

* Reward Redemptions

Historical transactions should remain associated with their original values.

For example, if a T-Shirt originally cost 2,000 credits and is later changed to 2,500 credits, an older completed redemption should continue to show that the student used **2,000 credits** at the time.

**21\. BASIC ANTI-ABUSE RULES**

Because credits can eventually be exchanged for rewards, basic safeguards will be implemented.

These include agreed validation such as:

* Maximum eligible reading time

* Valid page ranges

* Valid book ownership

* Subscription eligibility

* Prevention of invalid/negative values

* Server-side credit calculation

However, this system relies on student-submitted reading information.

The system cannot independently guarantee that a student actually read the submitted pages or spent the claimed amount of time reading unless a separate verification mechanism is developed.

**22\. EDITING / DELETING READING RECORDS**

A specific policy must be approved for previously submitted reading activities.

Questions to finalize:

**Can students edit an activity after submission?**

Yes / No

**Can students delete an activity after credits have been earned?**

Yes / No

If editing/deleting is allowed, the system will need defined rules for reversing and recalculating previously generated credits.

Recommended approach:

Once credits have been generated from a reading activity, students should not be able to freely modify credit-affecting information. Any corrections can be handled through an approved workflow/admin action.

**23\. DATE & BACKDATED ENTRIES**

The system must define whether students can submit reading activity for previous dates.

Possible rules:

* Only today

* Today \+ previous day

* Previous X days

* Any date

The selected rule should be finalized before development.

Future-dated reading activities will not be accepted.

**?24-28**

**29\. REWARD INVENTORY**

The basic reward module manages reward availability and redemption.

Full inventory management is not included unless specifically requested.

For example, the following would require additional scope:

* T-Shirt stock by size

* Purchase management

A simple Active/Inactive reward status can be provided within the core module

Reporting