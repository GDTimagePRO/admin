using Nop.Web.Framework.Mvc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.AdminFeatures.Models
{
    public class ButtonsModel : BaseNopModel
    {
        public class Button
        {
            public Button(string Controller, string Action, string Route, string Text, bool Select = false, bool Import = false)
            {
                _controller = Controller;
                _action = Action;
                _route = Route;
                _text = Text;
                _select = Select;
                _import = Import;
            }

            private string _controller;
            private string _action;
            private string _route;
            private string _text;
            private bool _select;
            private bool _import;

            public string Controller { get { return _controller; } }
            public string Action { get { return _action; } }
            public string Route { get { return _route; } }
            public string Text { get { return _text; } }
            public bool Select { get { return _select; } }
            public bool Import { get { return _import; } }
        }

        public Button pickingSlipsButton = null;
        public Button pickingSlipsAllButton = null;
        public Button upsExportButton = null;
        public Button upsExportAllButton = null;
        public Button upsImportButton = null;

        public List<Button> getAllButtons()
        {
            List<Button> buttons = new List<Button>();
            buttons.Add(pickingSlipsButton);
            buttons.Add(pickingSlipsAllButton);
            buttons.Add(upsExportButton);
            buttons.Add(upsExportAllButton);
            buttons.Add(upsImportButton);
            return buttons;
        }
    }
}
