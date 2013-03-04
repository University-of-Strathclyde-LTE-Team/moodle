M.deadline_extensions = {};

M.deadline_extensions.init_select_picker = function(Y, left_list, right_list, left_list_hidden, right_list_hidden) {

    M.deadline_extensions.opt = {};

    M.deadline_extensions.opt = new OptionTransfer(left_list , right_list);
    M.deadline_extensions.opt.setAutoSort(true);
    M.deadline_extensions.opt.setDelimiter(",");
    M.deadline_extensions.opt.saveNewLeftOptions(left_list_hidden);
    M.deadline_extensions.opt.saveNewRightOptions(right_list_hidden);

    for (var i=0; i<document.forms.length; i++) {
        if (document.forms[i].className == "mform") {
            M.deadline_extensions.opt.init(document.forms[i]);

        }
    }

};