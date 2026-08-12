# Voice Assistant Chatbot (Gemini-powered)

A simple Arabic voice assistant web app. The user speaks into the microphone,
the browser transcribes the speech, sends it to a PHP backend, which calls
the Google Gemini API and returns a reply that is shown in the chat and
read aloud with text-to-speech.

## Project structure

```
voice-assistant/
├── index.html          # Chat UI
├── style.css            # Styling
├── app.js                # Speech recognition, TTS, and fetch() calls to the backend
├── config.example.php   # Template for API key config (safe to commit)
├── config.php            # Real API key config (NOT committed — see .gitignore)
├── .htaccess              # Blocks direct browser access to config.php
├── .gitignore              # Excludes config.php from Git
├── api/
│   └── chat.php             # Backend endpoint that calls the Gemini API
└── README.md
```

## The problem I found

The frontend (`app.js`) sends every user message to a relative URL:

```js
const BACKEND_URL = "api/chat.php";
```

However, the uploaded project only contained the **frontend** files
(`index.html`, `style.css`, `app.js`) and two `.htaccess` files meant to
protect a `config.php` — but **neither `api/chat.php` nor `config.php`
actually existed**. Every time the user spoke, the browser's `fetch()`
request to `api/chat.php` hit a 404 (file not found), which is exactly why
the UI showed:

> "حدث خطأ أثناء الاتصال بالخادم، حاول مجدداً"
> (An error occurred while contacting the server, try again)

This was not a bug in existing PHP code — the backend that connects the
frontend to Gemini was simply missing.

## How I fixed it

1. **Created `config.php`** — stores the Gemini API key and model name in
   one place, kept out of Git and blocked from direct browser access by
   `.htaccess`.
2. **Created `api/chat.php`** — the missing backend:
   - Accepts only `POST` requests with JSON body `{ "prompt": "..." }`.
   - Validates the input and the presence of a real API key, returning
     clear JSON error messages instead of failing silently.
   - Calls the Gemini API (`generateContent`) using cURL, with a
     `file_get_contents` fallback for hosts where cURL is disabled.
   - Extracts the reply text and returns it as `{ "reply": "..." }`,
     matching exactly what `app.js` expects (`data.reply`).
3. **Added `.htaccess` at the project root** to block direct access to
   `config.php` so the API key can never be viewed by visiting its URL.
4. **Added `config.example.php` and `.gitignore`** so the project can be
   pushed to GitHub publicly without ever leaking the real API key.

No changes were needed in `index.html`, `style.css`, or `app.js` — the
frontend was already correctly written to talk to `api/chat.php`; it just
needed that file to exist and work.

## Deployment steps (InfinityFree)

1. Log in to the InfinityFree control panel and open **File Manager**
   (or connect via FTP with FileZilla using your account's FTP credentials).
2. Navigate to the `htdocs` folder of your hosting account.
3. Upload all project files and folders into `htdocs`, keeping the
   structure above (in particular, `chat.php` must stay inside an `api`
   subfolder).
4. Rename/copy `config.example.php` to `config.php` on the server (or
   just edit the `config.php` that is already in this package) and replace
   `YOUR_GEMINI_API_KEY_HERE` with a real key from
   [Google AI Studio](https://aistudio.google.com/app/apikey).
5. Confirm `.htaccess` was uploaded (some FTP clients hide dotfiles by
   default — make sure "show hidden files" is enabled).
6. Visit your site URL (e.g. `https://yourdomain.infinityfreeapp.com/`),
   allow microphone access when prompted, and test the assistant.

## Publishing to GitHub

1. Create a new repository on GitHub (e.g. `voice-assistant-chatbot`).
2. From the project folder, run:
   ```bash
   git init
   git add .
   git commit -m "Add missing PHP backend (api/chat.php) to fix server connection error"
   git branch -M main
   git remote add origin https://github.com/<your-username>/voice-assistant-chatbot.git
   git push -u origin main
   ```
3. Because `config.php` is listed in `.gitignore`, only `config.example.php`
   (with a placeholder key) gets pushed — the real API key stays only on
   the server, never in the public repo.

## Testing locally (optional, XAMPP/WAMP)

1. Copy the project folder into `htdocs` (XAMPP) or `www` (WAMP).
2. Make sure the PHP `curl` extension is enabled in `php.ini`
   (`extension=curl`), then restart Apache.
3. Set a real API key in `config.php`.
4. Open `http://localhost/voice-assistant/` in Chrome or Edge and test.
