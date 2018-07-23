package workstation.util;

import java.awt.Graphics2D;
import java.awt.geom.AffineTransform;
import java.awt.image.BufferedImage;
import java.io.ByteArrayInputStream;
import java.io.ByteArrayOutputStream;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStream;

import javax.imageio.ImageIO;

import com.itextpdf.text.BaseColor;
import com.itextpdf.text.Document;
import com.itextpdf.text.DocumentException;
import com.itextpdf.text.Element;
import com.itextpdf.text.Image;
import com.itextpdf.text.Rectangle;
import com.itextpdf.text.Utilities;
import com.itextpdf.text.pdf.BaseFont;
import com.itextpdf.text.pdf.ICC_Profile;
import com.itextpdf.text.pdf.PRStream;
import com.itextpdf.text.pdf.PdfArray;
import com.itextpdf.text.pdf.PdfContentByte;
import com.itextpdf.text.pdf.PdfICCBased;
import com.itextpdf.text.pdf.PdfIndirectReference;
import com.itextpdf.text.pdf.PdfName;
import com.itextpdf.text.pdf.PdfNumber;
import com.itextpdf.text.pdf.PdfObject;
import com.itextpdf.text.pdf.PdfPTable;
import com.itextpdf.text.pdf.PdfReader;
import com.itextpdf.text.pdf.PdfSpotColor;
import com.itextpdf.text.pdf.PdfStamper;
import com.itextpdf.text.pdf.PdfTemplate;
import com.itextpdf.text.pdf.PdfWriter;
import com.itextpdf.text.pdf.parser.PdfImageObject;
import com.vaadin.server.VaadinService;
import com.vaadin.server.StreamResource.StreamSource;

public class Pdf implements StreamSource {
    /**
	 * 
	 */
	private static final long serialVersionUID = -5234597299617139717L;
	private final ByteArrayOutputStream os = new ByteArrayOutputStream();

	private Document document;
	private com.itextpdf.text.pdf.PdfContentByte canvas;
	private com.itextpdf.text.pdf.PdfContentByte underCanvas;
	private Rectangle _size;
	public PdfWriter writer;
	private PdfSpotColor sp_red = new PdfSpotColor("Red", new BaseColor(255, 0 , 0));

    public Pdf(Rectangle size) {
        this(size, false);
    }
    
    public Pdf(Rectangle size, Boolean useColorProfile) {
    	document = null;
        _size = size;
        try {
            document = new Document(size);
            writer = PdfWriter.getInstance(document, os);
            document.open();
            if (useColorProfile) {
            	setColorProfile(writer);
            }
            canvas = writer.getDirectContent();
            underCanvas = writer.getDirectContentUnder();
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    public Pdf(Rectangle size, int r, int g, int b) {
        this(size, r, g, b, false);
    }
    
    public Pdf(Rectangle size, int r, int g, int b, boolean useColorProfile) {
    	this(size, useColorProfile);
    	setBackgroundColour(r, g, b);
    }
    
    private void setColorProfile(PdfWriter writer) throws IOException {
    	ICC_Profile icc = ICC_Profile.getInstance(new FileInputStream(VaadinService.getCurrent().getBaseDirectory().getAbsolutePath() + "/WEB-INF/color_profile/sRGB_IEC61966-2.1.icc"));
        writer.setOutputIntents("Custom", "", "http://www.color.org", "sRGB IEC61966-2.1", icc);
        PdfICCBased stream = new PdfICCBased(icc);
        PdfIndirectReference ref = writer.addToBody(stream).getIndirectReference();
        writer.setDefaultColorspace(new PdfName("sRGB IEC61966-2.1"), ref);
        PdfArray iccarray = new PdfArray();
		iccarray.add(PdfName.ICCBASED);
		iccarray.add(ref);
		PdfIndirectReference csRef = writer.addToBody(iccarray).getIndirectReference();
		writer.setDefaultColorspace(PdfName.DEFAULTRGB, csRef);
    }
    
    public PdfArray getSeparationColorspace(PdfWriter writer) {
    	PdfArray array = new PdfArray(PdfName.SEPARATION);
    	array.add(new PdfName("red"));
    	array.add(new PdfName("ICCBasedRGB"));
    	return array;
    }
    
    private void setBackgroundColour(int r, int g, int b) {
    	underCanvas.setRGBColorFill(r, g, b);
    	underCanvas.moveTo(0, _size.getTop());
    	underCanvas.lineTo(0, 0);
    	underCanvas.lineTo(_size.getRight(), 0);
    	underCanvas.lineTo(_size.getRight(), _size.getTop());
    	underCanvas.lineTo(0, _size.getTop());
    	underCanvas.fill();
    }
    
    public void addRectangleUnder(float w, float h, float x, float y, int r, int g, int b) {
    	underCanvas.setRGBColorFill(r, g, b);
    	underCanvas.rectangle(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y), Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	underCanvas.fill();
    }
    
    public void addElement(Element e) throws DocumentException {
    	document.add(e);
    }
    
    public float getStringWidth(String str, float fontSize) throws DocumentException, IOException {
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	return Utilities.pointsToMillimeters(bf.getWidthPoint(str, fontSize));
    }
    
    public void addTextAt(String text, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.moveText(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.setFontAndSize(bf, 8);
    	canvas.showText(text);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addTextAt(String text, float fontSize, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.moveText(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.setFontAndSize(bf, fontSize);
    	canvas.showText(text);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addTextAt(String text, float fontSize, float rotation, float x, float y) throws DocumentException, IOException {
    	canvas.saveState();
    	BaseFont bf = BaseFont.createFont(BaseFont.HELVETICA, BaseFont.CP1252, BaseFont.NOT_EMBEDDED);
    	canvas.beginText();
    	canvas.setFontAndSize(bf, fontSize);
    	canvas.showTextAligned(PdfContentByte.ALIGN_LEFT, text, Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y), rotation);
    	canvas.endText();
    	canvas.restoreState();
    }
    
    public void addImageAt(Image image, float x, float y) throws DocumentException {
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public void addImageAt(Image image, float x, float y, float w, float h) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public Image cropImage(Image image, float x1, float y1, float x2, float y2) throws DocumentException
    {
    	float origWidth = image.getScaledWidth();
        float origHeight = image.getScaledHeight();
        PdfTemplate t = canvas.createTemplate(Utilities.millimetersToPoints(x2),Utilities.millimetersToPoints(y2));
        t.addImage(image, origWidth, 0, 0, origHeight, Utilities.millimetersToPoints(-x1), Utilities.millimetersToPoints(-y1));
        return Image.getInstance(t);
    }
    
    public void addImageRotatedAt(Image image, float x, float y, float w, float h, float rotation) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	image.setRotationDegrees(rotation);
    	canvas.addImage(image);
    }
    
    public void addImageAt(Image image, float x, float y, float w, float h, float scale) throws DocumentException {
    	image.scaleAbsolute(Utilities.millimetersToPoints(w), Utilities.millimetersToPoints(h));
    	image.scalePercent(scale);
    	image.setAbsolutePosition(Utilities.millimetersToPoints(x), Utilities.millimetersToPoints(y));
    	canvas.addImage(image);
    }
    
    public void addLineAt(float lineWidth, float x1, float y1, float x2, float y2) {
    	canvas.saveState();
    	canvas.setLineWidth(Utilities.millimetersToPoints(lineWidth));
    	canvas.moveTo(Utilities.millimetersToPoints(x1), Utilities.millimetersToPoints(y1));
    	canvas.lineTo(Utilities.millimetersToPoints(x2), Utilities.millimetersToPoints(y2));
    	canvas.setRGBColorStroke(0xFF, 0, 0);
    	canvas.stroke();
    	canvas.restoreState();
    }
    
    public void addLineAtSpotColor(float lineWidth, float x1, float y1, float x2, float y2) {
    	canvas.saveState();
    	canvas.setLineWidth(Utilities.millimetersToPoints(lineWidth));
    	canvas.moveTo(Utilities.millimetersToPoints(x1), Utilities.millimetersToPoints(y1));
    	canvas.lineTo(Utilities.millimetersToPoints(x2), Utilities.millimetersToPoints(y2));
    	canvas.setColorStroke(sp_red, 1.0f);
    	canvas.stroke();
    	canvas.restoreState();
    }
    
    public void addLineAt(float lineWidth, float x1, float y1, float x2, float y2, int r, int g, int b) {
    	canvas.saveState();
    	canvas.setLineWidth(Utilities.millimetersToPoints(lineWidth));
    	canvas.moveTo(Utilities.millimetersToPoints(x1), Utilities.millimetersToPoints(y1));
    	canvas.lineTo(Utilities.millimetersToPoints(x2), Utilities.millimetersToPoints(y2));
    	canvas.setRGBColorStroke(r, g, b);
    	canvas.stroke();
    	canvas.restoreState();
    }
    
    public void addNewPage() {
    	document.newPage();
    }
    
    public void addNewPage(int r, int g, int b) {
    	document.newPage();
    	setBackgroundColour(r, g, b);
    }
    
    public Document getDocument() {
    	return document;
    }
    
    public void close() {
    	document.close();
    	System.gc();
    }
    
    public static class CompressedPdf implements StreamSource {

    	private final Pdf pdf;
    	
    	public CompressedPdf(Pdf pdf) {
    		this.pdf = pdf;
    	}
    	
    	private static ByteArrayInputStream manipulatePdf(InputStream input) throws IOException, DocumentException {
            //PdfName key = new PdfName("ITXT_SpecialId");
            //PdfName value = new PdfName("123456789");
            ByteArrayOutputStream output = new ByteArrayOutputStream();
            // Read the file
            PdfReader reader = new PdfReader(input);
            int n = reader.getXrefSize();
            PdfObject object;
            PRStream stream;
            // Look for image and manipulate image stream
            for (int i = 0; i < n; i++) {
                object = reader.getPdfObject(i);
                if (object == null || !object.isStream())
                    continue;
                stream = (PRStream)object;
               // if (value.equals(stream.get(key))) {
                PdfObject pdfsubtype = stream.get(PdfName.SUBTYPE);
                if (pdfsubtype != null && pdfsubtype.toString().equals(PdfName.IMAGE.toString())) {
                    PdfImageObject image = new PdfImageObject(stream);
                    BufferedImage bi = image.getBufferedImage();
                    if (bi == null) continue;
                    int width = (int)(bi.getWidth() * 1.0f);
                    int height = (int)(bi.getHeight() * 1.0f);
                    BufferedImage img = new BufferedImage(width, height, BufferedImage.TYPE_INT_RGB);
                    AffineTransform at = AffineTransform.getScaleInstance(1.0f, 1.0f);
                    Graphics2D g = img.createGraphics();
                    g.drawRenderedImage(bi, at);
                    ByteArrayOutputStream imgBytes = new ByteArrayOutputStream();
                    ImageIO.write(img, "JPG", imgBytes);
                    stream.clear();
                    stream.setData(imgBytes.toByteArray(), false, PRStream.BEST_COMPRESSION);
                    stream.put(PdfName.TYPE, PdfName.XOBJECT);
                    stream.put(PdfName.SUBTYPE, PdfName.IMAGE);
                    //stream.put(key, value);
                    stream.put(PdfName.FILTER, PdfName.DCTDECODE);
                    stream.put(PdfName.WIDTH, new PdfNumber(width));
                    stream.put(PdfName.HEIGHT, new PdfNumber(height));
                    stream.put(PdfName.BITSPERCOMPONENT, new PdfNumber(8));
                    stream.put(PdfName.COLORSPACE, PdfName.DEVICERGB);
                }
            }
            // Save altered PDF
            PdfStamper stamper = new PdfStamper(reader, output);
            stamper.close();
            reader.close();
            return new ByteArrayInputStream(output.toByteArray());
        }
    	
		@Override
		public InputStream getStream() {
			try {
				return manipulatePdf(pdf.getStream());
			} catch (IOException | DocumentException e) {
				return null;
			}
		}
    	
    }
    
    
    

    @Override
    public InputStream getStream() {
        // Here we return the pdf contents as a byte-array
    	canvas.getPdfWriter().createXmpMetadata();
        return new ByteArrayInputStream(os.toByteArray());
    }

	public void add(Element e) throws DocumentException {
		document.add(e);
	}
}