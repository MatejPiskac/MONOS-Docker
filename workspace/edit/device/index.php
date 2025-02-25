<?php

    # MONOS --- MONitoring Open-source Solution
    # MONOS --- MONitoring Over Snmp
    # MONOS --- Mobile Optimal Network Open-source System
    # There is more than you could imagine ... MONOS

    //include "snmp.php";
    include "../../main.php";

    if (isset($_GET['device'])) {
        $device = $_GET['device'];

        $conditions = ["id" => $device];
        $deviceName = findValueByConditions($devices, $conditions, "name");
    
    }

    if (isset($_SESSION['profile'])) {
        $profile = $_SESSION['profile'];

        $conditions = ["id" => $profile];
        $profileName = findValueByConditions($profiles, $conditions, "name");
    
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../../style.css">
<link rel="icon" type="image/x-icon" href="../../favicon.ico">
<title>MONOS</title>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script type="text/javascript">

    function toggleSidebar() {
        if ($(".sidebar-wrap").css("right").includes("-")) {
            $(".sidebar-wrap").animate({ right: '0%' }, 500);

            $(".close-menu").addClass("show");

            $(".darken").show(0);
            $(".darken").animate({opacity: 0.5}, 500);
        } else {
            $(".sidebar-wrap").animate({ right: '-100%' }, 500);

            $(".close-menu").removeClass("show");

            $(".darken").animate({opacity: 0}, 500).after().hide(0);
        }
    }


    function loaded() {

        $(".add").click(function() {
            $(this).children(".roll").fadeToggle(200);
        });

        $(".open-menu").click(toggleSidebar);
        $(".close-menu").click(toggleSidebar);
    }



    // PASTE
    
    $(document).ready(function () {
        const dropdownContent = $('.dropdown-content');
        const selectedItemsContainer = $('.selected-items-container');
        const dropdownInput = $('.dropdown-input');
        const addButton = $('.add-button');
        const inputContainer = $('.input-container');
        const dropdownArrow = $('.dropdown-arrow');
        const hiddenInputsContainer = $('.hidden-inputs');

        // Handle PHP-added items (if they exist)
        dropdownContent.find('input[type="checkbox"]:checked').each(function () {
            const checkbox = $(this);
            addItem(checkbox.closest('label').data("item"), checkbox.closest('label').data("id"), false);
        });

        // Show input field and filter items
        addButton.click(function () {
            inputContainer.css("display", "flex");
            addButton.hide();
            dropdownInput.focus();
            filterItems();
        });

        dropdownInput.on('input focus', filterItems);

        dropdownArrow.click(function (event) {
            event.stopPropagation();
            dropdownContent.toggleClass('show');
            $(this).find("img").toggleClass('rotated-180');
        });

        // Dynamic event binding for checkboxes (handles PHP-injected items)
        dropdownContent.on('change', 'input[type="checkbox"]', function () {
            const checkbox = $(this);
            const label = checkbox.closest('label');
            const value = label.data("item");
            const id = label.data("id");

            if (checkbox.is(':checked')) {
                addItem(value, id, true);
            } else {
                removeItem(id);
            }
        });

        // Handle Enter key selection
        dropdownInput.keydown(function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const firstUnchecked = dropdownContent.find('input[type="checkbox"]:visible:not(:checked)').first();
                if (firstUnchecked.length) {
                    firstUnchecked.prop('checked', true).trigger('change');
                    dropdownInput.val('');
                    dropdownContent.find('label').show();
                }
            }
        });

        // Function to filter dropdown items
        function filterItems() {
            const searchTerm = dropdownInput.val().toLowerCase();
            dropdownContent.find('label').each(function () {
                const item = $(this).data('item').toLowerCase();
                const isSelected = isItemSelected($(this).data('id'));
                $(this).toggle(item.includes(searchTerm) && !isSelected);
            });
            dropdownContent.addClass('show');
        }

        // Add selected item
        function addItem(value, id, focusInput) {
            if (isItemSelected(id)) return; // Prevent duplicates

            const item = $(`
                <div class="selected-item" data-id="${id}">
                    ${value} <span class="remove-item">x</span>
                </div>
            `);
            const hiddenInput = $(`<input type="hidden" name="profiles[]" value="${id}">`);

            item.find('.remove-item').click(() => removeItem(id));

            selectedItemsContainer.find(".add-button").before(item);
            hiddenInputsContainer.append(hiddenInput);

            if (focusInput) dropdownInput.focus();
        }

        // Remove selected item
        function removeItem(id) {
            selectedItemsContainer.find(`.selected-item[data-id="${id}"]`).remove();
            hiddenInputsContainer.find(`input[value="${id}"]`).remove();
            dropdownContent.find(`input[type="checkbox"][value="${id}"]`).prop('checked', false);
        }

        // Helper to check if an item is selected
        function isItemSelected(id) {
            return hiddenInputsContainer.find(`input[value="${id}"]`).length > 0;
        }

        // Close dropdown when clicking outside
        $(document).click(function (event) {
            if (!$(event.target).closest('.dropdown').length) {
                dropdownContent.removeClass('show');
                inputContainer.hide();
                addButton.show();
            }
        });

        dropdownInput.click(event => event.stopPropagation());


        hideLoad();
    });

</script>
<script src="../../scripts/main.js"></script>
</head>
<body>
<div id="loading">
    <div class="logo-img"></div>
</div>
    <div class="navbar">
        <a href="../../">
            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="currentColor"><path d="M400-80 0-480l400-400 71 71-329 329 329 329-71 71Z"/></svg>
        </a>
        <div class="path">
            <a href="../../"><svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="20px" fill="currentColor"><path d="M240-200h120v-240h240v240h120v-360L480-740 240-560v360Zm-80 80v-480l320-240 320 240v480H520v-240h-80v240H160Zm320-350Z"/></svg></a>
            <?php
                if (isset($profileName)) {
                    echo "
                        <a href='../../?profile={$profile}'>{$profileName}</a>
                    ";
                }

                if (isset($deviceName)) {
                    echo "
                        <a href='../../device/?profile={$profile}&device={$device}'>{$deviceName}</a>
                    ";
                }
            ?>
        </div>
        <?php
            if ($USER == "admin") {
                echo "
                <a href=\"../../admin\" class=\"admin-tools\">
                    ADMIN
                </a>";
            }
        ?>
    </div>
    <div class="all">
        <div class="header">
            <h1>MONOS</h1>
        </div>
        <div class="content">
            <?php echo isset($_GET["device"]) ? editDevice(true) : editDevice(false) ?>
        </div>
        <div class="sidebar-wrap">
            <div class="sidebar">
                <div class="sidebar-content">
                    <?php
                        if ($USER == "admin") {
                            echo '
                            <div>
                                <div class="title up">Add</div>
                                <div class="roll">
                                    <a href="../../edit/profile">
                                        <div class="add-img">Add profile</div>
                                    </a>
                                    <a href="../../edit/device">
                                        <div class="add-img">Add device</div>
                                    </a>
                                </div>
                            </div>';
                        }
                    ?>
                    <div>
                        <div class="title up">Manage</div>
                        <div class="roll">
                            <?php
                                if ($USER == "admin") {
                                    $adminTools = "
                                    <a href=\"../../admin\">
                                        <div class=\"users\">Users</div>
                                    </a>";

                                    echo $adminTools;
                                }
                            ?>
                            <a href="../../account/">
                                <div class="person">Profile</div>
                            </a>
                        </div>
                    </div>
                </div>
                <img src="../../icons/close-menu.png" class="close-menu" alt="close-menu">
                <div class="btm-bar">
                    <a href="?logout" class="log-out">Log out</a>
                </div>
            </div>
        </div>
        <div class="footer">
            <?php
                if ($USER == "admin") {
                    echo '
                    <div class="small add">
                        <img src="../../icons/plus.png" alt="">
                        <div class="roll pop-add">
                            <div>
                                <a href="../../edit/profile/">
                                    <img src="../../icons/plus.png" alt="">
                                    <div class="add-img">Add profile</div>
                                </a>
                                <a href="../../edit/device/">
                                    <img src="../../icons/plus.png" alt="">
                                    <div class="add-img">Add device</div>
                                </a>
                            </div>
                        </div>
                    </div>';
                } else {
                    echo '
                    <a href="../../account">
                        <img src="../../icons/account.svg" alt="">
                    </a>';
                }
            ?>
            <a href="../../">
                <img src="../../icons/home.png" alt="">
            </a>
            <div class="small open-menu">
                <img src="../../icons/menu.png" alt="">
            </div>
        </div>
    </div>
    <div class="darken"></div>
</body>
</html>