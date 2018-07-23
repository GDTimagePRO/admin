using Nop.Core.Domain.Orders;
using Nop.Services.Messages;
using Nop.Services.Orders;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Nop.Plugin.Widgets.AdminFeatures.Services
{
    class AdminOrderService
    {

        private readonly IOrderService _orderService;
        private readonly IWorkflowMessageService _workflowMessageService;


        public AdminOrderService(IOrderService orderService, IWorkflowMessageService workflowMessageService)
        {
            _orderService = orderService;
            _workflowMessageService = workflowMessageService;
        }

        /// <summary>
        /// Sets an order status
        /// </summary>
        /// <param name="order">Order</param>
        /// <param name="os">New order status</param>
        /// <param name="notifyCustomer">True to notify customer</param>
        protected void SetOrderStatus(Order order,
            OrderStatus os, bool notifyCustomer)
        {
            if (order == null)
                throw new ArgumentNullException("order");

            OrderStatus prevOrderStatus = order.OrderStatus;
            if (prevOrderStatus == os)
                return;

            //set and save new order status
            order.OrderStatusId = (int)os;
            _orderService.UpdateOrder(order);

            //order notes, notifications
            order.OrderNotes.Add(new OrderNote()
            {
                Note = string.Format("Order status has been changed to {0}", os.ToString()),
                DisplayToCustomer = false,
                CreatedOnUtc = DateTime.UtcNow
            });
            _orderService.UpdateOrder(order);


            if (prevOrderStatus != OrderStatus.Complete &&
                os == OrderStatus.Complete
                && notifyCustomer)
            {
                //notification
                int orderCompletedCustomerNotificationQueuedEmailId = _workflowMessageService.SendOrderCompletedCustomerNotification(order, order.CustomerLanguageId);
                if (orderCompletedCustomerNotificationQueuedEmailId > 0)
                {
                    order.OrderNotes.Add(new OrderNote()
                    {
                        Note = string.Format("\"Order completed\" email (to customer) has been queued. Queued email identifier: {0}.", orderCompletedCustomerNotificationQueuedEmailId),
                        DisplayToCustomer = false,
                        CreatedOnUtc = DateTime.UtcNow
                    });
                    _orderService.UpdateOrder(order);
                }
            }

            if (prevOrderStatus != OrderStatus.Cancelled &&
                os == OrderStatus.Cancelled
                && notifyCustomer)
            {
                //notification
                int orderCancelledCustomerNotificationQueuedEmailId = _workflowMessageService.SendOrderCancelledCustomerNotification(order, order.CustomerLanguageId);
                if (orderCancelledCustomerNotificationQueuedEmailId > 0)
                {
                    order.OrderNotes.Add(new OrderNote()
                    {
                        Note = string.Format("\"Order cancelled\" email (to customer) has been queued. Queued email identifier: {0}.", orderCancelledCustomerNotificationQueuedEmailId),
                        DisplayToCustomer = false,
                        CreatedOnUtc = DateTime.UtcNow
                    });
                    _orderService.UpdateOrder(order);
                }
            }
        }

        /// <summary>
        /// Sets order as pending
        /// </summary>
        /// <param name="order">Order</param>
        /// <param name="notifyCustomer">True to notify customer</param>
        public void MarkOrderAsPending(Order order, bool notifyCustomer)
        {
            if (order == null)
                throw new ArgumentNullException("order");

            //Cancel order
            SetOrderStatus(order, OrderStatus.Pending, notifyCustomer);

            _orderService.UpdateOrder(order);
        }
    }
}
