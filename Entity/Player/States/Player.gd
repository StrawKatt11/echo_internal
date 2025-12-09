extends CharacterBody2D

#region Player variables

@onready var sprite: Sprite2D = $Sprite
@onready var collider: CollisionShape2D = $Collider
@onready var animator: AnimationPlayer = $Animator
@onready var States: Node = $StateMachine

#Physics
const RunSpeed = 250
const Acceleration = 40
const Decelaration = 50
const GravityJump = 600
const GravityFall = 750
const MaxFallVelocity = -300
const JumpVelocity = -280
const MaxJumps = 1

var moveSpeed = RunSpeed
var jumpSpeed = JumpVelocity
var moveDirectionX = 0
var jumps = 0
var facing = 1

#Input
var keyUp = false
var keyDown = false
var keyLeft = false
var keyRight = false
var keyJump = false
var keyJumpPressed = false

#State Machine
var currentState = null
var previousState = null


#endregion

#region Main Loop Function

func _ready():
	for state in States.get_children():
		state.States = States
		state.Player = self
	previousState = States.Fall
	currentState = States.Fall

func _draw():
	currentState.Draw()

func _physics_process(delta: float):
	
	GetInputStates()
	
	
	currentState.Update(delta)
	
	
	HorizontalMovement()
	HandleJump()
	
	
	move_and_slide()
	


func ChangeState(newState):
	if (newState != null):
		previousState = currentState
		currentState = newState
		previousState.ExitState()
		currentState.EnterState()
		#print("State Change From: " + previousState.Name + " To " + currentState.Name)
#endregion

#region Custom Functions


func GetInputStates():
	
	keyUp = Input.is_action_pressed("Up")
	keyDown = Input.is_action_pressed("Down")
	keyLeft = Input.is_action_pressed("Left")
	keyRight = Input.is_action_pressed("Right")
	keyJump = Input.is_action_pressed("Jump")
	keyJumpPressed = Input.is_action_just_pressed("Jump")
	
	if (keyRight): facing = 1
	if (keyLeft): facing = -1

func die():
	print("player meghalt")
	global_position = Vector2(44,-22)

func HorizontalMovement(acceleration: float = Acceleration,decelaration: float = Decelaration):
	moveDirectionX = Input.get_axis("Left","Right")
	if (moveDirectionX != 0):
		velocity.x = move_toward(velocity.x, moveDirectionX * moveSpeed, Acceleration)
	else:
		velocity.x = move_toward(velocity.x, moveDirectionX * moveSpeed, Decelaration)

func HandleFalling():
	if (!is_on_floor()):
		ChangeState(States.Fall)

func HandleMaxFallVelocity():
	if (velocity.y > MaxFallVelocity): velocity.y * MaxFallVelocity

func HandleLanding():
	if(is_on_floor()):
		jumps = 0
		ChangeState(States.Idle)

func HandleGravity(delta, gravity: float = GravityJump):
	if (!is_on_floor()):
		velocity.y += gravity * delta

func HandleJump():
	if (keyJumpPressed and jumps < MaxJumps):
			jumps += 1
			ChangeState(States.Jump)


func HandleFlipH():
	sprite.flip_h = (facing < 0)
	
#endregion
