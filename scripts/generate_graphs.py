import numpy as np
import pandas as pd
import matplotlib.pyplot as plt

# NPF to GB Conversion
# Load the performance metrics data from the CSV file
file_path_npf2gb_metrics = './npf2gb_metrics.csv'  # Replace with your CSV file path
performance_data_npf2gb = pd.read_csv(file_path_npf2gb_metrics)
# Generate graph for Execution Time
plt.figure(figsize=(16, 8))
plt.subplot(211)

x = performance_data_npf2gb['Iteration']
y = performance_data_npf2gb['Conversion Time (ms)']
plt.plot(x, y, label='Execution Time (ms)')

xlabels = ['1', '300000', '600000', '900000']
xproportion = np.arange(0, 1000000, 300000)
plt.xticks(xproportion, xlabels)

plt.ylabel('Execution Time (ms)')
plt.title('Execution Time NPF > Gb')
plt.legend()

plt.subplot(212)
# convert bytes to megabytes
y = [round(x / (1024 * 1024)) for x in performance_data_npf2gb['Memory Used (bytes)']]
plt.plot(performance_data_npf2gb['Iteration'], y, label='Memory Used (MB)', color='blue')
plt.xticks(xproportion, xlabels)
plt.xlabel('Iteration')
plt.ylabel('Memory Used (MB)')
plt.title('Memory Usage NPF > Gb')
plt.legend()
plt.grid(True)
plt.show()

# GB to NPF Conversion
# Load the performance metrics data from the CSV file
file_path_gb2npf_metrics = './gb2npf_metrics.csv'  # Replace with your CSV file path
performance_data_gb2npf = pd.read_csv(file_path_gb2npf_metrics)
# Generate graph for Execution Time
plt.figure(figsize=(16, 8))
plt.subplot(211)
plt.xticks(xproportion, xlabels)
plt.plot(performance_data_gb2npf['Iteration'], performance_data_gb2npf['Conversion Time (ms)'], label='Execution Time (ms)')
plt.ylabel('Execution Time (ms)')
plt.title('Execution Time Gb > NPF')
plt.legend()

# Generate graph for Memory Usage in Megabytes
plt.subplot(212)
y_mb = [round(x / (1024 * 1024)) for x in performance_data_gb2npf['Memory Used (bytes)']]
plt.plot(performance_data_gb2npf['Iteration'], y_mb, label='Memory Used (MB)', color='blue')
plt.xlabel('Iteration')
plt.xticks(xproportion, xlabels)
plt.ylabel('Memory Used (MB)')
plt.title('Memory Usage Gb > NPF')
plt.legend()
plt.grid(True)
plt.show()
