extends Node2D

var elapsed_time: float = 0.0
var running: bool = false

@onready var time_label: Label = $Time

func _ready():
	start()

func _process(delta: float) -> void:
	if running:
		elapsed_time += delta
		time_label.text = format_time(elapsed_time)

func format_time(seconds: float) -> String:
	var minutes = int(seconds) / 60
	var secs = int(seconds) % 60
	var millis = int((seconds - int(seconds)) * 100)
	return "%02d:%02d.%02d" % [minutes, secs, millis]

func start():
	running = true

func stop():
	running = false

func reset():
	elapsed_time = 0.0
	time_label.text = format_time(0.0)
