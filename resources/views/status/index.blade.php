<!DOCTYPE html>
<!--
    This file renders the full "Driftsstatus" (Operational Status) page.

    It displays a list of carriers (e.g., transport partners), each shown as a
    row with: logo, name, status indicator, and a short status message.

    Each row includes a toggle button (+ / .). When clicked, the row expands
    and reveals an extra details section underneath.

    The page combines:
    - Blade templating (looping through $carriers)
    - Custom CSS for layout, columns, and status design
    - A small JavaScript script that handles expand/collapse interactions
-->
<html lang="da">
<head>
    <meta charset="utf-8">
    <title>Driftsstatus</title>

    <style>
        /* Removes default browser margin around the page
           - Makes a beige background to match Homerunners theme */
        body {
            margin: 0;
            background: #f7f3ee;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        /* Centers the content on the page and limits max width
            - Max width prevents the content from stretching too wide
            - Margin: 0 auto centers the container horizontally
            - Padding is the space around all the content */
        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px 40px;
        }

        /* Styling for the page headline "DRIFTSSTATUS" */
        .page-title {
            font-size: 34px;
            font-weight: 800; /* Extra bold */
            letter-spacing: 0.05em; /* Slight spacing between letters */
            color: #05324d; /* Dark blue */
            margin: 32px 0 24px; /* Top, horizontal, bottom spacing */
        }

        /* A vertical list of status rows (one for each carrier)
           - Display enables gap spacing
           - Flex direction stacks children vertically
           - Gap makes a space between each row*/
        .status-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        /* Each individual carrier row
           - border-radius makes rounded corners
           - padding makes inner spacing
           - display flex makes content align horizontally
           */
        .status-row {
            background: #ffffff; /* white */
            border-radius: 8px;
            padding: 20px 24px;
            display: flex;
            align-items: center; /* Vertically centers elements */
            box-shadow: 0 0 0 1px #ece7df; /* Thin border-like outline */
        }

        /* Logo container — creates a fixed-size box for the image
           - border radius makes a slightly rounded logo box
           - margin right makes space between logo and name
           */
        .carrier-logo {
            width: 64px;
            height: 64px;
            border-radius: 6px;
            display: flex;
            align-items: center; /* Center logo vertically */
            justify-content: center; /* Center logo horizontally */
            margin-right: 24px;
            font-weight: 700; /* (Used when no image is present) */
            color: #444;
            font-size: 18px;
        }

        /* Makes sure logos scale nicely inside the box
           - max-width prevents oversizing horizontally
           - max-height prevents oversizing vertically
           */
        .carrier-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Keeps proportions; no cropping */
            display: block;/* Removes inline spacing under image */
        }

        /* Name of the carrier — fixed width keeps columns aligned */
        .carrier-name {
            font-weight: 800;
            color: #05324d;
            width: 180px; /* Aligns names in the column */
        }

        /* Container for the status pill (green/red dot + label)
           - display flex aligns the dot + label in one row
           - gap makes spacing between dot and text
           - width: keeps column alignment
           */
        .status-pill {
            display: flex;
            align-items: center; /* Vertically aligned */
            gap: 8px;
            width: 110px;
            font-size: 14px;
        }

        /* Generic dot shape for status indicator */
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 999px; /* makes it a circle */
        }

        /* Green "up" status */
        .status-dot--up {
            background: #1cc461;
        }

        /* Red "down" status */
        .status-dot--down {
            background: #f04438;
        }

        /* Status text next to the dot */
        .status-label {
            color: #444;
        }

        /* The middle exception message column
           - flex: 1 makes it stretch and take all remaining space
            margin makes horizontal spacing from status + toggle (button to the right)
            */
        .message {
            flex: 1;
            margin: 0 24px;
        }

        /* Light blue rounded exception message bubble
           - border-radius makes a fully rounded pill shape
           - padding is the inner space
           - display: inline-flex keeps the bubble sized to content
           - gap: space between icon and text spacing
           */
        .message-box {
            background: #f3f7fb; /* Light blue background */
            border-radius: 999px;
            padding: 10px 16px;
            font-size: 14px;
            color: #364152; /* Muted dark text */
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        /* Small circular “i” info icon inside blue bubble */
        .message-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%; /* Circle */
            border: 1px solid #c0cedd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #7b90a8;
        }

        /* Expand/collapse button (the little + icon)
           - border-radius creates a round button
           - font-size decides the size of the + and -
           - cursor: pointer shows a "clickable" cursor
           */
        .toggle-btn {
            width: 32px;
            height: 32px;
            border-radius: 50%; /* Round button */
            border: 1px solid #c0cedd;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #7b90a8;
            cursor: pointer;
        }

        /* The hidden extra info under each row
           when you click on the circle with a +/-
           - display: none is hidden until expanded
           */
        .details {
            display: none;
            padding: 0 24px 12px 112px;
            /* Matches indentation under name column */
            font-size: 14px;
            color: #555;
        }

        /* When a row has class .expanded, show its .details block */
        .status-row.expanded + .details {
            display: block;
        }
    </style>

    <script>
        /* Wait until the entire HTML document is loaded.
           This ensures all buttons and rows exist before we try to use them. */
        document.addEventListener('DOMContentLoaded', function () {
            /* Select all elements with the class "toggle-btn".
               querySelectorAll returns a list we can loop through. */
            document.querySelectorAll('.toggle-btn').forEach(function (btn) {
                /* Add a click event listener to each button.
                   When the user clicks the button, the function below runs.*/
                btn.addEventListener('click', function () {
                    /* Find the nearest parent element with the class "status-row".
                   closest() climbs up the DOM tree until it finds the right row.
                   This row is the one we want to expand or collapse.*/
                    const row = btn.closest('.status-row');
                    /* Toggle the "expanded" class on the row.
                       If the class is not present → add it.
                       If the class is already present → remove it.
                       CSS can then show or hide extra content based on this class.*/
                    row.classList.toggle('expanded');
                    /* Change the button text.
                       If the row is expanded, show a minus (−).
                       If the row is collapsed, show a plus (+).
                       This gives a clear visual indicator for the user.*/
                    btn.textContent = row.classList.contains('expanded') ? '−' : '+';
                });
            });
        });
    </script>
</head>
<body>
<!-- PAGE WRAPPER: Contains the whole page content + title of the page -->
<div class="page-wrapper">
    <h1 class="page-title">DRIFTSSTATUS</h1>

    <!-- STATUS LIST: Loop through all carriers -->
    <div class="status-list">
        @foreach($carriers as $carrier)

            <!-- STATUS ROW: One row per carrier -->
            <div class="status-row">

                <!-- CARRIER LOGO -->
                <div class="carrier-logo">
                    <img
                        src="{{ asset('images/carriers/' . $carrier['logo']) }}"
                        alt="{{ $carrier['name'] }} logo"
                    >
                </div>

                <!-- CARRIER NAME -->
                <div class="carrier-name">
                    {{ $carrier['name'] }}
                </div>

                <!-- STATUS PILL (Dot + Label) -->
                <div class="status-pill">
                    <span class="status-dot {{ $carrier['has_issue'] ? 'status-dot--down' : 'status-dot--up' }}"></span>
                    <span class="status-label">Status</span>
                </div>

                <!-- MAIN STATUS MESSAGE (EXCEPTIONS) -->
                <div class="message">
                    <!-- CASE: Carrier has an issue -->
                    @if($carrier['has_issue'])
                        <div class="message-box">
                            <span class="message-icon">i</span>
                            <span>{{ $carrier['message'] }}</span>
                        </div>
                        <!-- CASE: No issue -->
                    @else
                        <div class="message-box" style="opacity: 0.7;">
                            <span class="message-icon">i</span>
                            <span>Ingen kendte problemer</span>
                        </div>
                    @endif
                </div>

                <!-- EXPAND/COLLAPSE BUTTON -->
                <button type="button" class="toggle-btn">+</button>
            </div>
            <!-- END .status-row -->

            <!-- COLLAPSIBLE DETAILS SECTION -->
            <div class="details">
                <!-- Placeholder for future API data -->
                Her kan der senere komme ekstra information for <strong>{{ $carrier['name'] }}</strong>
                (f.eks. mere detaljeret fejlbeskrivelse).
            </div>
        @endforeach
    </div>
    <!-- END .status-list -->

</div>
<!-- END .page-wrapper -->
</body>
</html>
