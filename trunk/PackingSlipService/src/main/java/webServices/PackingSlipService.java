
package webServices;

import java.io.IOException;
import java.io.OutputStream;

import generators.PdfGenerator;

import javax.servlet.ServletContext;
import javax.ws.rs.GET;
import javax.ws.rs.Path;
import javax.ws.rs.Produces;
import javax.ws.rs.QueryParam;
import javax.ws.rs.WebApplicationException;
import javax.ws.rs.core.Context;
import javax.ws.rs.core.StreamingOutput;

import org.codehaus.jackson.map.ObjectMapper;


@Path("/packing-slips")
public class PackingSlipService {
	@Context
	ServletContext context;
	ObjectMapper mapper = new ObjectMapper();
	
	
	@Path("/pdf")
	@GET
	@Produces({"application/pdf"})
    public StreamingOutput getPdf(@QueryParam("data") String orderData) throws Exception {
		final Order[] orders = mapper.readValue(orderData, Order[].class);
		return new StreamingOutput() {
	        public void write(OutputStream output) throws IOException, WebApplicationException {
	            try {
	            	PdfGenerator generator = new PdfGenerator();
	                generator.createPdf(output, orders, context);
	            } catch (Exception e) {
	                throw new WebApplicationException(e);
	            }
	        }
	    };
    }
}
