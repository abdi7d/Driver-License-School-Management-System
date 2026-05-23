/**
 * Chart Utilities - Pure Canvas-based charting library
 * No external dependencies - uses HTML5 Canvas API
 * 
 * Includes:
 * - BarChart class for bar charts
 * - PieChart class for pie charts
 * - Helper functions for data transformation and drawing
 */

// ============================================================================
// CONSTANTS
// ============================================================================

const CHART_COLORS = [
  '#3b82f6', // Blue (Motorcycle)
  '#10b981', // Green (Private Car)
  '#f59e0b', // Orange (Heavy Truck)
  '#8b5cf6', // Purple (Bus)
  '#ef4444'  // Red (Transport)
];

const TOOLTIP_BG = '#1f2937';
const TOOLTIP_BORDER = '#374151';
const TOOLTIP_TEXT = '#ffffff';
const AXIS_COLOR = '#d1d5db';
const GRID_COLOR = '#f3f4f6';
const TEXT_COLOR = '#374151';

// ============================================================================
// BAR CHART CLASS
// ============================================================================

class BarChart {
  /**
   * Create a new BarChart instance
   * @param {HTMLCanvasElement} canvas - Canvas element to draw on
   * @param {Object} data - Chart data { labels, values, colors }
   * @param {Object} options - Chart options { title, padding, width, height }
   */
  constructor(canvas, data, options = {}) {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
    this.data = data;
    this.options = {
      title: options.title || 'Bar Chart',
      padding: options.padding || 60,
      width: canvas.width,
      height: canvas.height,
      ...options
    };
    
    this.hoveredBarIndex = -1;
    this.tooltipData = null;
    
    // Bind event listeners
    this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
    this.canvas.addEventListener('mouseleave', () => this.handleMouseLeave());
  }

  /**
   * Draw the entire bar chart
   */
  draw() {
    // Clear canvas
    this.ctx.fillStyle = '#ffffff';
    this.ctx.fillRect(0, 0, this.options.width, this.options.height);

    // Calculate dimensions
    this.calculateDimensions();

    // Draw grid and axes
    this.drawGrid();
    this.drawAxes();

    // Draw bars
    this.drawBars();

    // Draw title
    this.drawTitle();

    // Draw labels
    this.drawLabels();

    // Draw tooltip if hovering
    if (this.tooltipData) {
      this.drawTooltip(this.tooltipData.barIndex, this.tooltipData.x, this.tooltipData.y);
    }
  }

  /**
   * Calculate chart dimensions and scaling
   */
  calculateDimensions() {
    const { padding, width, height } = this.options;
    const { values } = this.data;

    this.chartLeft = padding;
    this.chartRight = width - padding;
    this.chartTop = padding + 40; // Extra space for title
    this.chartBottom = height - padding - 40; // Extra space for labels

    this.chartWidth = this.chartRight - this.chartLeft;
    this.chartHeight = this.chartBottom - this.chartTop;

    this.maxValue = Math.max(...values);
    this.barCount = values.length;
    this.barWidth = this.chartWidth / this.barCount * 0.7;
    this.barSpacing = this.chartWidth / this.barCount * 0.3;
    this.yScale = this.chartHeight / this.maxValue;
  }

  /**
   * Draw grid lines
   */
  drawGrid() {
    const { ctx, chartLeft, chartRight, chartTop, chartBottom, maxValue } = this;
    const gridLines = 5;

    ctx.strokeStyle = GRID_COLOR;
    ctx.lineWidth = 1;

    for (let i = 0; i <= gridLines; i++) {
      const y = chartBottom - (i * chartBottom - chartTop) / gridLines;
      ctx.beginPath();
      ctx.moveTo(chartLeft, y);
      ctx.lineTo(chartRight, y);
      ctx.stroke();
    }
  }

  /**
   * Draw axes (X and Y)
   */
  drawAxes() {
    const { ctx, chartLeft, chartRight, chartTop, chartBottom } = this;

    ctx.strokeStyle = AXIS_COLOR;
    ctx.lineWidth = 2;

    // Y-axis
    ctx.beginPath();
    ctx.moveTo(chartLeft, chartTop);
    ctx.lineTo(chartLeft, chartBottom);
    ctx.stroke();

    // X-axis
    ctx.beginPath();
    ctx.moveTo(chartLeft, chartBottom);
    ctx.lineTo(chartRight, chartBottom);
    ctx.stroke();

    // Y-axis labels (values)
    ctx.fillStyle = TEXT_COLOR;
    ctx.font = '12px Arial';
    ctx.textAlign = 'right';
    ctx.textBaseline = 'middle';

    for (let i = 0; i <= 5; i++) {
      const value = Math.round((this.maxValue / 5) * i);
      const y = chartBottom - (i * (chartBottom - chartTop) / 5);
      ctx.fillText(value, chartLeft - 10, y);
    }
  }

  /**
   * Draw the bars
   */
  drawBars() {
    const { ctx, data, chartLeft, chartBottom, barWidth, barSpacing, yScale } = this;
    const { values, colors } = data;

    values.forEach((value, index) => {
      const barHeight = value * yScale;
      const x = chartLeft + index * (barWidth + barSpacing) + barSpacing / 2;
      const y = chartBottom - barHeight;

      // Draw bar
      ctx.fillStyle = colors[index] || CHART_COLORS[index];
      ctx.fillRect(x, y, barWidth, barHeight);

      // Draw border
      ctx.strokeStyle = 'rgba(0, 0, 0, 0.1)';
      ctx.lineWidth = 1;
      ctx.strokeRect(x, y, barWidth, barHeight);

      // Highlight if hovering
      if (index === this.hoveredBarIndex) {
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.lineWidth = 3;
        ctx.strokeRect(x - 2, y - 2, barWidth + 4, barHeight + 4);
      }
    });
  }

  /**
   * Draw chart title
   */
  drawTitle() {
    const { ctx, options } = this;

    ctx.fillStyle = TEXT_COLOR;
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    ctx.fillText(options.title, this.options.width / 2, 10);
  }

  /**
   * Draw X-axis labels (program names)
   */
  drawLabels() {
    const { ctx, data, chartLeft, chartBottom, barWidth, barSpacing } = this;
    const { labels } = data;

    ctx.fillStyle = TEXT_COLOR;
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';

    labels.forEach((label, index) => {
      const x = chartLeft + index * (barWidth + barSpacing) + barSpacing / 2 + barWidth / 2;
      const y = chartBottom + 10;

      // Wrap long labels
      const maxWidth = barWidth + barSpacing;
      this.drawWrappedText(ctx, label, x, y, maxWidth);
    });
  }

  /**
   * Draw wrapped text (for long labels)
   */
  drawWrappedText(ctx, text, x, y, maxWidth) {
    const words = text.split(' ');
    let line = '';
    let lineY = y;

    words.forEach((word, index) => {
      const testLine = line + (line ? ' ' : '') + word;
      const metrics = ctx.measureText(testLine);

      if (metrics.width > maxWidth && line) {
        ctx.fillText(line, x, lineY);
        line = word;
        lineY += 15;
      } else {
        line = testLine;
      }
    });

    ctx.fillText(line, x, lineY);
  }

  /**
   * Get bar index at mouse position
   */
  getBarAtPoint(x, y) {
    const { chartLeft, chartBottom, barWidth, barSpacing, yScale, data } = this;
    const { values } = data;

    for (let i = 0; i < values.length; i++) {
      const barX = chartLeft + i * (barWidth + barSpacing) + barSpacing / 2;
      const barHeight = values[i] * yScale;
      const barY = chartBottom - barHeight;

      if (x >= barX && x <= barX + barWidth && y >= barY && y <= chartBottom) {
        return i;
      }
    }

    return -1;
  }

  /**
   * Handle mouse move event
   */
  handleMouseMove(e) {
    const rect = this.canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const barIndex = this.getBarAtPoint(x, y);

    if (barIndex !== this.hoveredBarIndex) {
      this.hoveredBarIndex = barIndex;
      this.tooltipData = barIndex >= 0 ? { barIndex, x, y } : null;
      this.draw();
    } else if (barIndex >= 0) {
      this.tooltipData = { barIndex, x, y };
      this.draw();
    }
  }

  /**
   * Handle mouse leave event
   */
  handleMouseLeave() {
    this.hoveredBarIndex = -1;
    this.tooltipData = null;
    this.draw();
  }

  /**
   * Draw tooltip for bar
   */
  drawTooltip(barIndex, x, y) {
    const { ctx, data, chartTop, chartLeft, chartBottom, barWidth, barSpacing, yScale } = this;
    const { labels, values } = data;

    const label = labels[barIndex];
    const value = values[barIndex];

    const tooltipText = `${label}: ${value}`;
    const padding = 8;
    const textWidth = ctx.measureText(tooltipText).width;
    const tooltipWidth = textWidth + padding * 2;
    const tooltipHeight = 24;

    // Calculate tooltip position (above the bar)
    const barX = chartLeft + barIndex * (barWidth + barSpacing) + barSpacing / 2;
    const barHeight = value * yScale;
    const barY = chartBottom - barHeight;

    let tooltipX = barX + barWidth / 2 - tooltipWidth / 2;
    let tooltipY = barY - tooltipHeight - 10;

    // Keep tooltip within canvas bounds
    if (tooltipX < 5) tooltipX = 5;
    if (tooltipX + tooltipWidth > this.options.width - 5) {
      tooltipX = this.options.width - tooltipWidth - 5;
    }
    if (tooltipY < 5) tooltipY = barY + barHeight + 10;

    // Draw tooltip background
    ctx.fillStyle = TOOLTIP_BG;
    ctx.fillRect(tooltipX, tooltipY, tooltipWidth, tooltipHeight);

    // Draw tooltip border
    ctx.strokeStyle = TOOLTIP_BORDER;
    ctx.lineWidth = 1;
    ctx.strokeRect(tooltipX, tooltipY, tooltipWidth, tooltipHeight);

    // Draw tooltip text
    ctx.fillStyle = TOOLTIP_TEXT;
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(tooltipText, tooltipX + tooltipWidth / 2, tooltipY + tooltipHeight / 2);
  }
}

// ============================================================================
// PIE CHART CLASS
// ============================================================================

class PieChart {
  /**
   * Create a new PieChart instance
   * @param {HTMLCanvasElement} canvas - Canvas element to draw on
   * @param {Object} data - Chart data { labels, values, colors, revenue }
   * @param {Object} options - Chart options { title, centerX, centerY, radius }
   */
  constructor(canvas, data, options = {}) {
    this.canvas = canvas;
    this.ctx = canvas.getContext('2d');
    this.data = data;
    this.options = {
      title: options.title || 'Pie Chart',
      centerX: options.centerX || canvas.width / 2,
      centerY: options.centerY || canvas.height / 2 - 20,
      radius: options.radius || 100,
      width: canvas.width,
      height: canvas.height,
      ...options
    };

    this.slices = [];
    this.hoveredSliceIndex = -1;
    this.tooltipData = null;

    // Bind event listeners
    this.canvas.addEventListener('mousemove', (e) => this.handleMouseMove(e));
    this.canvas.addEventListener('mouseleave', () => this.handleMouseLeave());
  }

  /**
   * Draw the entire pie chart
   */
  draw() {
    // Clear canvas
    this.ctx.fillStyle = '#ffffff';
    this.ctx.fillRect(0, 0, this.options.width, this.options.height);

    // Calculate slices
    this.calculateSlices();

    // Draw title
    this.drawTitle();

    // Draw slices
    this.drawSlices();

    // Draw legend
    this.drawLegend();

    // Draw tooltip if hovering
    if (this.tooltipData) {
      this.drawTooltip(this.tooltipData.sliceIndex, this.tooltipData.x, this.tooltipData.y);
    }
  }

  /**
   * Calculate slice angles and positions
   */
  calculateSlices() {
    const { data } = this;
    const { values } = data;
    const total = values.reduce((sum, val) => sum + val, 0);

    this.slices = [];
    let currentAngle = -Math.PI / 2; // Start at top

    values.forEach((value, index) => {
      const sliceAngle = (value / total) * 2 * Math.PI;
      const percentage = ((value / total) * 100).toFixed(1);

      this.slices.push({
        index,
        startAngle: currentAngle,
        endAngle: currentAngle + sliceAngle,
        percentage,
        value
      });

      currentAngle += sliceAngle;
    });
  }

  /**
   * Draw pie slices
   */
  drawSlices() {
    const { ctx, options, data, slices } = this;
    const { centerX, centerY, radius } = options;
    const { colors } = data;

    slices.forEach((slice, index) => {
      const { startAngle, endAngle } = slice;

      // Draw slice
      ctx.beginPath();
      ctx.moveTo(centerX, centerY);
      ctx.arc(centerX, centerY, radius, startAngle, endAngle);
      ctx.lineTo(centerX, centerY);
      ctx.fillStyle = colors[index] || CHART_COLORS[index];
      ctx.fill();

      // Draw border
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2;
      ctx.stroke();

      // Highlight if hovering
      if (index === this.hoveredSliceIndex) {
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.3)';
        ctx.lineWidth = 3;
        ctx.stroke();
      }

      // Draw percentage label
      const labelAngle = (startAngle + endAngle) / 2;
      const labelRadius = radius * 0.65;
      const labelX = centerX + Math.cos(labelAngle) * labelRadius;
      const labelY = centerY + Math.sin(labelAngle) * labelRadius;

      ctx.fillStyle = '#ffffff';
      ctx.font = 'bold 12px Arial';
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillText(slice.percentage + '%', labelX, labelY);
    });
  }

  /**
   * Draw chart title
   */
  drawTitle() {
    const { ctx, options } = this;

    ctx.fillStyle = TEXT_COLOR;
    ctx.font = 'bold 16px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'top';
    ctx.fillText(options.title, this.options.width / 2, 10);
  }

  /**
   * Draw legend below pie chart
   */
  drawLegend() {
    const { ctx, data, options } = this;
    const { labels, colors, revenue } = data;
    const { centerX, centerY, radius } = options;

    const legendY = centerY + radius + 40;
    const itemHeight = 20;
    const itemWidth = this.options.width / labels.length;

    ctx.font = '12px Arial';
    ctx.textBaseline = 'middle';

    labels.forEach((label, index) => {
      const x = (index * itemWidth) + 20;
      const y = legendY;

      // Draw color box
      ctx.fillStyle = colors[index] || CHART_COLORS[index];
      ctx.fillRect(x, y - 6, 12, 12);

      // Draw label
      ctx.fillStyle = TEXT_COLOR;
      ctx.textAlign = 'left';
      ctx.fillText(label, x + 18, y);
    });
  }

  /**
   * Get slice index at mouse position
   */
  getSliceAtPoint(x, y) {
    const { options } = this;
    const { centerX, centerY, radius } = options;

    // Calculate distance from center
    const dx = x - centerX;
    const dy = y - centerY;
    const distance = Math.sqrt(dx * dx + dy * dy);

    // Check if point is within pie radius
    if (distance > radius) return -1;

    // Calculate angle using atan2 (returns -π to π, with 0 pointing right)
    let angle = Math.atan2(dy, dx);
    
    // Find slice at this angle
    for (let i = 0; i < this.slices.length; i++) {
      const slice = this.slices[i];
      const { startAngle, endAngle } = slice;
      
      // Check if angle is within slice range
      // Handle the case where slice might wrap around -π/π boundary
      if (startAngle <= endAngle) {
        // Normal case: slice doesn't wrap
        if (angle >= startAngle && angle <= endAngle) {
          return i;
        }
      } else {
        // Slice wraps around the -π/π boundary
        if (angle >= startAngle || angle <= endAngle) {
          return i;
        }
      }
    }

    return -1;
  }

  /**
   * Handle mouse move event
   */
  handleMouseMove(e) {
    const rect = this.canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    const sliceIndex = this.getSliceAtPoint(x, y);

    if (sliceIndex !== this.hoveredSliceIndex) {
      this.hoveredSliceIndex = sliceIndex;
      this.tooltipData = sliceIndex >= 0 ? { sliceIndex, x, y } : null;
      this.draw();
    } else if (sliceIndex >= 0) {
      this.tooltipData = { sliceIndex, x, y };
      this.draw();
    }
  }

  /**
   * Handle mouse leave event
   */
  handleMouseLeave() {
    this.hoveredSliceIndex = -1;
    this.tooltipData = null;
    this.draw();
  }

  /**
   * Draw tooltip for slice
   */
  drawTooltip(sliceIndex, x, y) {
    const { ctx, data } = this;
    const { labels, revenue } = data;

    const label = labels[sliceIndex];
    const amount = revenue[sliceIndex];
    const tooltipText = `${label}: ETB ${amount.toLocaleString()}`;

    const padding = 8;
    const textWidth = ctx.measureText(tooltipText).width;
    const tooltipWidth = textWidth + padding * 2;
    const tooltipHeight = 24;

    let tooltipX = x - tooltipWidth / 2;
    let tooltipY = y - tooltipHeight - 10;

    // Keep tooltip within canvas bounds
    if (tooltipX < 5) tooltipX = 5;
    if (tooltipX + tooltipWidth > this.options.width - 5) {
      tooltipX = this.options.width - tooltipWidth - 5;
    }
    if (tooltipY < 5) tooltipY = y + 10;

    // Draw tooltip background
    ctx.fillStyle = TOOLTIP_BG;
    ctx.fillRect(tooltipX, tooltipY, tooltipWidth, tooltipHeight);

    // Draw tooltip border
    ctx.strokeStyle = TOOLTIP_BORDER;
    ctx.lineWidth = 1;
    ctx.strokeRect(tooltipX, tooltipY, tooltipWidth, tooltipHeight);

    // Draw tooltip text
    ctx.fillStyle = TOOLTIP_TEXT;
    ctx.font = '12px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(tooltipText, tooltipX + tooltipWidth / 2, tooltipY + tooltipHeight / 2);
  }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Transform API data for bar chart
 * @param {Array} programs - Programs array from API
 * @returns {Object} Bar chart data
 */
function calculateBarChartData(programs) {
  return {
    labels: programs.map(p => p.name),
    values: programs.map(p => p.enrolled),
    colors: CHART_COLORS
  };
}

/**
 * Transform API data for pie chart
 * @param {Array} programs - Programs array from API
 * @returns {Object} Pie chart data
 */
function calculatePieChartData(programs) {
  const revenue = programs.map(p => p.enrolled * p.price);

  return {
    labels: programs.map(p => p.name),
    values: revenue,
    colors: CHART_COLORS,
    revenue: revenue
  };
}

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { BarChart, PieChart, calculateBarChartData, calculatePieChartData };
}
