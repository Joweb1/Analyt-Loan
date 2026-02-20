Based on the documents provided, Analyt Loan is a specialized SaaS (Software as a Service) platform designed as a "Self-Driving Operating System" for micro-lenders and cooperative societies. Its primary goal is to replace manual processes—like spreadsheets and physical notebooks—with an automated, intelligent system that manages the entire lending lifecycle.

## 1. The Core Philosophy: "Self-Driving Money"
The concept centers on the idea that the software should do the heavy lifting so the lender doesn't have to. It operates on three main pillars:
- **The Memory:** It perfectly tracks every loan, borrower, and due date.
- **The Nudge:** It automatically contacts borrowers via Email(PHP Mailer) to remind them of payments, reducing the "forgetfulness" that leads to bad debt.
- **The Engine:** A calculation system that automates interest rates (flat or reducing balance), fees, and penalties with zero manual input.

## 2. Problems It Solves
The documentation highlights that small lenders often lose up to 20% of their capital due to poor organization. Analyt Loan addresses:
- **Blind Spots:** Eliminates the need to manually check books to see who owes money.
- **Manual Labor:** Automates "chasing" borrowers, which is usually the most time-consuming part of lending.
- **Risk Management:** Calculates a "Trust Score" for borrowers based on their payment history to help lenders make better decisions.

## 3. Key Features & Tools
| Feature | Function |
|---|---|
| Automated Email | Acts as an automated collections agent, sending reminders and updates. |
| Trust Score | A proprietary metric that identifies high-risk vs. reliable borrowers. |
| Omnibar Search | A "Google-like" search bar to find any borrower or loan instantly. |
| Nightly Cron Jobs | Backend automation that updates loan statuses and triggers penalties overnight. |

## 4. Technical Structure
The system is built for speed and scale using a MYSQL database for data integrity and Material Design (Roboto Flex) for a clean, intuitive user interface. It is structured into five core sections:
- **Dashboard (Home):** For instant business insights and charts.
- **People (CRM):** To manage borrower profiles.
- **Loans (Portfolio):** To track active and historical lending.
- **Tasks (Collections):** A focused view of who needs to be contacted.
- **Settings (Admin):** For system configuration.

## 5. Frontend Development
To ensure the application remains lightweight and performs optimally, we will be using vanilla Javascript for all frontend interactions. The use of Alpine.js is strictly prohibited. This approach gives us full control over the user experience and avoids unnecessary dependencies.

## Deployment (cPanel / Shared Hosting without SSH)

This project is configured for automated deployment via GitHub Actions. Since SSH is not available, follow these steps to set up your shared hosting:

1.  **FTP Setup:** Create an FTP account in cPanel and point its root to your project directory (e.g., `/home/username/analyt-loan`).
2.  **GitHub Secrets:** Add the following secrets to your GitHub repository (`Settings > Secrets and variables > Actions`):
    *   `FTP_SERVER`: Your hosting FTP host (e.g., `ftp.yourdomain.com`).
    *   `FTP_USERNAME`: Your FTP username.
    *   `FTP_PASSWORD`: Your FTP password.
    *   `FTP_SERVER_DIR`: The remote directory path where the app is uploaded (default is `./`).
3.  **Database:** Create a MySQL database and user in cPanel.
4.  **Environment:** Manually upload your `.env` file to the server root or ensure the GitHub Action includes it.
5.  **Post-Deployment Setup:** After the files are uploaded, visit the following URL in your browser to run migrations and link storage:
    *   `https://yourdomain.com/deploy-setup.php?token=setup_XXXXX`
    *   *(Check the script `public/deploy-setup.php` for the token logic or use the hint on the page).*
6.  **Cleanup:** **IMPORTANT!** Delete `public/deploy-setup.php` from your server immediately after the setup is complete.

### **Shared Hosting Tips:**
*   **Folder Structure:** It is recommended to upload the entire project to a folder outside `public_html` (e.g., `/home/username/analyt-loan`) and then create a symbolic link from `public_html` to the `public` folder of your project. 
    *   Command: `ln -s /home/username/analyt-loan/public /home/username/public_html` (If you have terminal access) or use the File Manager.
*   **Database access:** Ensure your hosting allows the web server to connect to `sql100.iceiy.com`.

## Summary
Analyt Loan is designed to make lending "as simple as sending an email." It targets the "missing middle" of finance—lenders who are too big for a notebook but too small for high-end enterprise banking software.
