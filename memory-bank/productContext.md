# Product Context

*   **Problem Solved:** Provides a simple, self-hosted way to know if recipients have opened emails, without relying on complex third-party services. Useful for sales outreach, newsletters, or important communications where confirmation of receipt/view is valuable.
*   **How it Works:**
    1.  User generates a unique tracking link/pixel via the application interface.
    2.  User embeds this pixel (a transparent 1x1 image) in their outgoing email.
    3.  When the recipient opens the email, their email client requests the image from the tracking URL.
    4.  The server logs this request as an "open" event, capturing details like timestamp and potentially associated metadata (recipient, subject - if provided during link generation).
    5.  The server returns the transparent pixel image.
    6.  The user can view the logged open events in a dashboard.
*   **User Experience Goals:** Simple setup, easy link generation, clear dashboard display of open events. Minimal configuration required.
