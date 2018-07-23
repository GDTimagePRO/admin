using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using MySql.Data.MySqlClient;
using System.Data.SqlClient;
using System.Data;
using System.Text.RegularExpressions;

namespace NOPOrderItemUpdate
{
    class Program
    {

        private static MySqlConnection MySQLConnect()
        {
            MySqlConnection connection;
            string server;
            string database;
            string uid;
            string password;
            server = "loucks51.arvixevps.com";
            database = "ssi_mr";
            uid = "test";
            password = "test123";
            string connectionString;
            connectionString = "SERVER=" + server + ";" + "DATABASE=" +
            database + ";" + "UID=" + uid + ";" + "PASSWORD=" + password + ";";

            connection = new MySqlConnection(connectionString);
            connection.Open();
            return connection;
        }

        private static SqlConnection SQLConnect()
        {
            SqlConnection thisConnection = new SqlConnection(@"Server=loucks51.arvixevps.com;Database=testnop;User Id=testnop;Password=testnop;");
            thisConnection.Open();
            return thisConnection;
        }

        private static int parseAttr(String attr)
        {
            //Order Id: thumbs.order_items/10855_prev.png
            int r = -1;
            if (Regex.IsMatch(attr, @"Order Id: thumbs\.order_items/[0-9]+_prev\.png"))
            {
               Regex reg = new Regex(@"[0-9]+");
               string num = reg.Match(attr).Value;
               r = int.Parse(num);
            }
            return r;
        }

        private static void DOQuery(MySqlConnection ssi_mr, SqlConnection nop)
        {
            String query = @"SELECT [Order].id, OrderProductVariant.AttributeDescription FROM [testnop].[dbo].[Order] JOIN OrderProductVariant ON [Order].id = OrderProductVariant.OrderId WHERE AttributeDescription LIKE '%thumbs.order_items%' AND ([Order].OrderStatusId = 20 OR [Order].OrderStatusId = 10) AND [Order].ShippingStatusId = 20 AND [Order].PaymentStatusId = 30";
            DataSet ds = new DataSet();
		 	SqlDataAdapter nopOrders = new SqlDataAdapter(query, nop);
            nopOrders.Fill(ds, "NOPOrders");
            System.Collections.Generic.Dictionary<int, int> q = new Dictionary<int, int>();
            

            query = @"SELECT * FROM order_items";
            MySqlDataAdapter da = new MySqlDataAdapter(query, ssi_mr);
            DataSet db = new DataSet();
            da.Fill(db, "order_items");
            var a = from row in ds.Tables[0].AsEnumerable()
                    join order in db.Tables[0].AsEnumerable()
                    on parseAttr(row.Field<String>("AttributeDescription")) equals
                       order.Field<int>("id")
                    select new
                    {
                        OrderId =
                            order.Field<int>("id"),
                        StageId =
                            order.Field<int>("processing_stages_id"),
                        NOPId =
                            row.Field<int>("id"),
                        ManId =
                            order.Field<int>("manufacturer_order_id")
                    };

            var b = from order in db.Tables[0].AsEnumerable()
                    where !ds.Tables[0].AsEnumerable().Any(r => parseAttr(r.Field<String>("AttributeDescription")) == order.Field<int>("id"))
                    && order.Field<int>("processing_stages_id") == 400
                    select order;
 
            Console.WriteLine("Grabbed data");
            
            foreach (var row in a)
            {
                bool dirty = false;
                int psid = row.StageId;
                int mid = row.ManId;
                query = @"UPDATE order_items SET processing_stages_id = {0}, manufacturer_order_id = {1} WHERE id = {2};";
                if(row.StageId == 300)
                {
                    psid = 350;
                    dirty = true;
                }

                if (mid == 0)
                {
                    int qi = 1;
                    int c = ds.Tables[0].AsEnumerable().Count(nopData => nopData.Field<int>("id") == row.NOPId);
                    if (c > 1)
                    {
                        if (q.ContainsKey(row.OrderId))
                            q.TryGetValue(row.OrderId, out qi);
                        else
                        {
                            q.Add(row.OrderId, qi);
                        }
                    }
                    mid = row.NOPId * 1000 + qi;
                    dirty = true;
                }
                else
                {
                    Console.WriteLine("Order item: " + row.OrderId + " has manufacturer order id of " + mid);
                }
                if (dirty)
                {
                    query = String.Format(query, psid, mid, row.OrderId);
                    MySqlCommand cmd = new MySqlCommand(query, ssi_mr);
                    cmd.ExecuteNonQuery();
                }
            }

            foreach (var row in b)
            {
                query = @"UPDATE order_items SET processing_stages_id = 300 WHERE id = {0}";
                query = String.Format(query, row.Field<int>("id"));
                MySqlCommand cmd = new MySqlCommand(query, ssi_mr);
                cmd.ExecuteNonQuery(); 
            }

            Console.WriteLine("Updated Data");
        }

        static void Main(string[] args)
        {
            MySqlConnection ssi_mr = null;
            SqlConnection nop = null;
            try
            {
                ssi_mr = MySQLConnect();
            }
            catch (Exception e)
            {
                Console.WriteLine("Failed to connect to MySQL database: " + e.Message);
        
            }
            Console.WriteLine("Connected to ssi_mr");
            try
            {
                nop = SQLConnect();
            }
            catch (Exception e)
            {
                Console.WriteLine("Failed to connect to SQL database: " + e.Message);
            }
            Console.WriteLine("Connection to testnop");
            DOQuery(ssi_mr, nop);
            ssi_mr.Close();
            nop.Close();
            while (true) ;
        }
    }
}
