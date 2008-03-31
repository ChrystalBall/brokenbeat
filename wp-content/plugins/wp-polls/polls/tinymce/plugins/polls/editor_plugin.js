tinyMCE.importPluginLanguagePack('polls');
var TinyMCE_PollsPlugin = {
	getInfo : function() {
		return {
			longname : 'WP-Polls',
			author : 'Lester Chan',
			authorurl : 'http://lesterchan.net',
			infourl : 'http://lesterchan.net/portfolio/programming.php',
			version : "2.21"
		};
	},
	getControlHTML : function(cn) {
		switch (cn) {
			case "polls":
				return tinyMCE.getButtonHTML(cn, 'lang_polls_desc', '{$pluginurl}/images/poll.gif', 'mcePollInsert');
		}
		return "";
	},
	execCommand : function(editor_id, element, command, user_interface, value) {
		switch (command) {
			case "mcePollInsert":
				tinyMCE.execInstanceCommand(editor_id, "mceInsertContent", false, insertPoll('visual', ''));
			return true;
		}
		return false;
	}
};
tinyMCE.addPlugin("polls", TinyMCE_PollsPlugin);