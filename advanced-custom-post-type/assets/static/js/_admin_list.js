import {useTranslation} from "./_admin_commons.js";
import {initSortable} from "./_admin_sortable.js";

var $ = jQuery.noConflict();

export const handleListFieldsEvents = () => {

    /**
     *
     * @param el
     */
    const scrollAndFocusTo = (el) => {
        if(el.length > 0){
            el[0].scrollIntoView({
                behavior: 'smooth'
            });

            el.find(".list-element").first().focus();
        }
    };

    // Keyboard shortcut
    document.addEventListener('keydown', (event) => {

        // Navigate element with Ctrl+Up / Ctrl+Up
        if(
            event.ctrlKey &&
            event.key === "ArrowUp" &&
            event.target.classList.contains("acpt-form-control") &&
            event.target.classList.contains("list-element")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            const parentRow = element.closest("tr");
            const prevRow = parentRow.prev("tr");

            if(prevRow.length > 0){
                scrollAndFocusTo(prevRow);
            }
        }

        if(
            event.ctrlKey &&
            event.key === "ArrowDown" &&
            event.target.classList.contains("acpt-form-control") &&
            event.target.classList.contains("list-element")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            const parentRow = element.closest("tr");
            const nextRow = parentRow.next("tr");

            if(nextRow.length > 0){
                scrollAndFocusTo(nextRow);
            }
        }

        // Delete element with Ctrl+Delete
        if(
            event.ctrlKey &&
            event.key === "Delete" &&
            event.target.classList.contains("acpt-form-control") &&
            event.target.classList.contains("list-element")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            const parentRow = element.closest("tr");

            if(parentRow.length > 0){

                const deleteButton = parentRow.find("a.list-remove-element");

                if(deleteButton.length > 0){
                    deleteButton.click();
                }
            }
        }

        // Add element with Ctrl+Enter
        if(
            event.ctrlKey &&
            event.key === "Enter" &&
            event.target.classList.contains("acpt-form-control") &&
            event.target.classList.contains("list-element")
        ) {
            event.stopPropagation();
            event.preventDefault();

            const element = $(event.target);
            const list = element.closest("table");

            if(list.length > 0){

                const addButton = list.next(".list-add-element");

                if(addButton.length > 0){
                    addButton.click();
                }
            }
        }
    });

    /**
     * Add list element
     */
    $('body').on('click', '.list-add-element', function(e) {

        e.preventDefault();

        const $this = $(this);
        const $targetId = $this.data('target-id');
        const $listWrapper = $this.prev('table').children('.list-wrapper');
        const $nextId = $listWrapper.find('tr').length;
        const $baseId = $this.data('parent-name');

        $listWrapper.append(`
            <tr id="${$baseId}_${$nextId}" class="list-element sortable-li sortable-li-${$targetId}">
                <td width="20">
                    <div class="handle">
                        .<br/>.<br/>.
                    </div>
                </td>
                <td>
                    <input id="${$baseId}_${$nextId}" name="${$baseId}[]" type="text" class="acpt-form-control list-element">
                </td>
                <td width="100">
                    <a class="list-remove-element" data-index="${$nextId}" data-parent-name="${$baseId}" data-target-id="${$baseId}_${$nextId}" href="#">${useTranslation('Remove element')}</a>
                </td>
            </tr>
        `);

        // scroll to last element added
        const lastElement = $listWrapper.find("tr").last();
        scrollAndFocusTo(lastElement);
        initSortable();
    });

    /**
     * Remove list element
     */
    $('body').on('click', 'a.list-remove-element', function(e) {

        e.preventDefault();

        const $this = $(this);
        const $targetId = $this.data('target-id');
        const $index = $this.data('index');
        const $target = document.getElementById($targetId);
        const list = $($target).closest("table");

        if($target){
            $target.remove();
        }

        // scroll to last element
        if(list.length > 0){

            const prevElement = list.find("tr").get($index-1);
            const $prevElement = $(prevElement);
            scrollAndFocusTo($prevElement);
        }
    });
};